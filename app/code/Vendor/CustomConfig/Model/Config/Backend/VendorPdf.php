<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Vnecoms\VendorsConfig\Model\ResourceModel\Config as ConfigResource;
use Vnecoms\VendorsConfig\Model\ResourceModel\Config\Collection as ConfigResourceCollection;
use Vnecoms\VendorsConfig\Helper\Data as VnecomsConfigHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Filesystem;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\App\ResourceConnection;

class VendorPdf extends \Vnecoms\VendorsConfig\Model\Config\Backend\File
{
    protected $vendorSession;
    protected $resourceConnection;
    protected $_logger;

    // Your PDF attribute_id in ves_vendor_entity_varchar
    protected $attributeId = 188; // You can override via constructor or config if needed

    public function __construct(
        Context $context,
        Registry $registry,
        ConfigResource $resource = null,
        ConfigResourceCollection $resourceCollection = null,
        VnecomsConfigHelper $configHelper,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UploaderFactory $uploaderFactory,
        RequestDataInterface $requestData,
        Serialize $serialize,
        Filesystem $filesystem,
        VendorSession $vendorSession,
        ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->vendorSession = $vendorSession;
        $this->resourceConnection = $resourceConnection;
        $this->_logger = $logger;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $configHelper,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $serialize,
            $filesystem,
            $data
        );

        file_put_contents(BP . '/var/log/vendor_pdf_debug.log', "✅ VendorPdf constructor called\n", FILE_APPEND);
    }

    /**
     * After Save PDF File
     */
    public function afterSave()
    {
        $logger = new \Zend_Log();
        $logger->addWriter(new \Zend_Log_Writer_Stream(BP . '/var/log/vendor_pdf_debug.log'));
        $logger->info('✅ afterSave() called for Vendor PDF');

        $vendorId = $this->getVendorId();
        $attributeId = $this->attributeId;

        if (!$vendorId) {
            $logger->info("⚠️ Vendor ID missing, skipping PDF save.");
            return parent::afterSave();
        }

        $value = $this->getValue();

        // Handle upload/delete
        if (is_array($value)) {
            if (!empty($value['delete'])) {
                $value = ''; // deleted
            } elseif (isset($value['value'])) {
                $value = $value['value']; // relative path
            } else {
                $value = '';
            }
        }

        if (!$value) {
            $logger->info("⚠️ Empty PDF value, skipping update.");
            return parent::afterSave();
        }

        // Auto-detect folder path from config (e.g. ves_vendors/attribute/certificate)
        $uploadDir = $this->_uploadDir ?? 'ves_vendors/attribute/certificate';
        if (strpos($value, $uploadDir) === false) {
            $value = $uploadDir . '/' . ltrim($value, '/');
        }

        // Ensure extension is PDF
        if (pathinfo($value, PATHINFO_EXTENSION) !== 'pdf') {
            $logger->info("❌ Invalid file type. Only PDF allowed. Value: $value");
            return parent::afterSave();
        }

        $logger->info("🟢 Final PDF value to save: " . $value);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ves_vendor_entity_varchar');

        // Check existing record
        $select = $connection->select()
            ->from($tableName, ['value_id'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', $attributeId);

        $exists = $connection->fetchOne($select);

        if ($exists) {
            $connection->update(
                $tableName,
                ['value' => $value],
                ['value_id = ?' => $exists]
            );
            $logger->info("🟢 Updated PDF for Vendor ID: {$vendorId}");
        } else {
            $connection->insert(
                $tableName,
                [
                    'entity_id' => $vendorId,
                    'attribute_id' => $attributeId,
                    'value' => $value
                ]
            );
            $logger->info("🟢 Inserted PDF for Vendor ID: {$vendorId}");
        }

        if (pathinfo($value, PATHINFO_EXTENSION) !== 'pdf') {
    $logger->info("❌ Invalid file type. Only PDF allowed. Value: $value");
    return parent::afterSave();
    }
    }

    /**
     * After Load PDF value
     */
    public function afterLoad()
    {
        $vendorId = $this->vendorSession && $this->vendorSession->getVendor()
            ? $this->vendorSession->getVendor()->getId()
            : null;

        if (!$vendorId) {
            $this->_logger->info("Vendor ID missing in afterLoad() for PDF.");
            return parent::afterLoad();
        }

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('ves_vendor_entity_varchar');

        $select = $connection->select()
            ->from($table, ['value'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', $this->attributeId)
            ->limit(1);

        $value = $connection->fetchOne($select);

        if ($value !== false && $value !== null) {
            $this->setValue($value);
            $this->_logger->info("Loaded vendor PDF for vendor ID: {$vendorId}");
        }

        return parent::afterLoad();
    }
}
