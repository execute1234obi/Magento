<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

class Website extends \Vnecoms\VendorsConfig\Model\Config
{
    protected $resourceConnection;
    protected $_vendorHelper;
    protected $vendorSession;
    protected $logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\VendorsConfig\Model\ResourceModel\Config $resource = null,
        \Vnecoms\VendorsConfig\Model\ResourceModel\Config\Collection $resourceCollection = null,
        \Vnecoms\VendorsConfig\Helper\Data $configHelper,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_vendorHelper = $vendorHelper;
        $this->resourceConnection = $resourceConnection;
        $this->vendorSession = $vendorSession; // ✅ FIXED: assign vendorSession
          // Create a custom logger for config_debug.log
        //$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/config_debug.log');
        //$this->logger = new \Zend_Log();
        //$this->logger->addWriter($writer);

        parent::__construct($context, $registry, $resource, $resourceCollection, $configHelper, $serialize, $data);
    }

    /**
     * ✅ Load Website URL from DB when opening the config form
     */
public function afterLoad()
{
    //$this->logger->info("✅ [Website] afterLoad() called");

$vendorId = null;
if ($this->vendorSession && $this->vendorSession->getVendor()) {
    $vendorId = $this->vendorSession->getVendor()->getId();
}
//$this->logger->info("Vendor ID from session: " . var_export($vendorId, true));
    // Load website value from DB
    try {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ves_vendor_entity_varchar');
        $attributeId = 178; // your website attribute_id

        $select = $connection->select()
            ->from($tableName, ['value'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', $attributeId);

        $value = $connection->fetchOne($select);
        //$this->logger->info("🌐 Loaded website value from DB: " . var_export($value, true));

        if ($value !== false && $value !== null) {
            $this->setValue($value);
            //$this->logger->info("✅ Website value set successfully.");
        } else {
            //$this->logger->info("ℹ️ No DB value found for vendor {$vendorId} and attribute {$attributeId}.");
        }
    } catch (\Exception $e) {
       // $this->logger->error("❌ DB fetch error: " . $e->getMessage());
    }

    return parent::afterLoad();
}


    /**
     * ✅ Save Website URL into DB
     */
    public function afterSave()
    {
        //$this->logger->info('✅ afterSave() called for Website URL');

        $vendorId = $this->vendorSession->getVendorId();
        //$this->logger->info('Vendor ID: ' . $vendorId);

        $value = $this->getValue();
        $attributeId = 178;

        if (!$vendorId) {
          //  $this->logger->warning('⚠️ Vendor ID missing in afterSave()');
            return parent::afterSave();
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ves_vendor_entity_varchar');

        $select = $connection->select()
            ->from($tableName)
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', $attributeId);
        $exists = $connection->fetchOne($select);

        if ($exists) {
            $connection->update(
                $tableName,
                ['value' => $value],
                ['entity_id = ?' => $vendorId, 'attribute_id = ?' => $attributeId]
            );
            //$this->logger->info("🔁 Updated existing Website URL for Vendor ID $vendorId");
        } else {
            $connection->insert(
                $tableName,
                [
                    'entity_id' => $vendorId,
                    'attribute_id' => $attributeId,
                    'value' => $value
                ]
            );
            //$this->logger->info("🆕 Inserted new Website URL for Vendor ID $vendorId");
        }

        return parent::afterSave();
    }
}
