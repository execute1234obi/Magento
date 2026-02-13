<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Psr\Log\LoggerInterface;

/**
 * Backend model: Sync vendor config form values into ves_vendor_entity table
 */
class SyncVendorData extends Value
{
    /**
     * @var VendorSession
     */
    protected $vendorSession;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        VendorSession $vendorSession,
        ResourceConnection $resource,
        LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resourceModel = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->vendorSession = $vendorSession;
        $this->resource      = $resource;
        $this->logger        = $logger;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resourceModel,
            $resourceCollection,
            $data
        );
    }

    /**
     * Before save hook
     */
    public function beforeSave()
    {
        $vendorId = $this->vendorSession->getVendorId();
        if (!$vendorId) {
            return parent::beforeSave();
        }

        // Map config path -> column name in ves_vendor_entity
        $map = [
            'vendor_general/general_info/b_name'            => 'b_name',
            'vendor_general/general_info/upload_logo'       => 'logo',
            'vendor_general/general_info/upload_banner'     => 'banner',
            'vendor_general/general_info/short_description' => 'short_description',
            'vendor_general/general_info/website'           => 'website',
            'vendor_general/general_info/contact_information' => 'contact_information',
        ];

        $path = $this->getPath();
        if (!isset($map[$path])) {
            return parent::beforeSave();
        }

        // Normalize value (handles image/file arrays)
        $value = $this->getValue();
        if (is_array($value)) {
            if (isset($value['value'])) {
                $value = $value['value'];
            } elseif (isset($value['name'])) {
                $value = $value['name'];
            } elseif (isset($value['delete']) && $value['delete'] == 1) {
                $value = '';
            } else {
                $value = '';
            }
        }

        $column = $map[$path];

        try {
            $connection = $this->resource->getConnection();
            $table      = $this->resource->getTableName('ves_vendor_entity');

            // Update vendor record directly
            $connection->update(
                $table,
                [$column => $value],
                ['entity_id = ?' => $vendorId]
            );
        } catch (\Exception $e) {
            $this->logger->error("Vendor data sync failed: " . $e->getMessage());
        }

        // Prevent saving into core_config_data / ves_vendor_config
        $this->unsetData('value');
        $this->setValue(null);

        return parent::beforeSave();
    }
}
