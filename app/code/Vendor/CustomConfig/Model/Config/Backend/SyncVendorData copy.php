<?php

namespace Vendor\CustomConfig\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\Vendors\Model\VendorFactory;
use Vnecoms\VendorsConfig\Helper\Data as ConfigHelper;

/**
 * Custom backend model to sync system configuration values to the Vendor EAV table.
 */
class SyncVendorData extends Value
{
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var VendorSession
     */
    protected $vendorSession;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param VendorFactory $vendorFactory
     * @param VendorSession $vendorSession
     * @param ConfigHelper $configHelper
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        VendorFactory $vendorFactory,
        VendorSession $vendorSession,
        ConfigHelper $configHelper,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vendorSession = $vendorSession;
        $this->configHelper = $configHelper;
        $this->resourceConnection = $resourceConnection;

        // Parent constructor call to ensure all parent dependencies are satisfied.
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * This method is executed before the configuration value is saved.
     * We will use it to sync the data to the vendor's EAV entity.
     *
     * @return $this
     */
    public function beforeSave()
    {
        // Get the current logged-in vendor ID from the session.
        $vendorId = $this->vendorSession->getVendorId();

        // If there's no vendor ID, we can't save to the EAV table, so we stop here.
        if (!$vendorId) {
            return parent::beforeSave();
        }

        // The full path of the configuration field from system.xml.
        $path = $this->getPath();

        // Map the config path to the corresponding Vendor EAV attribute code.
        $map = [
            'vendor_general/general_info/b_name' => 'b_name',
            'vendor_general/general_info/upload_logo' => 'upload_logo',
            'vendor_general/general_info/upload_banner' => 'upload_banner',
            'vendor_general/general_info/short_description' => 'short_description',
            'vendor_general/general_info/website' => 'website',
        ];

        // Check if the current field is in our map. If not, we don't need to sync it.
        if (!isset($map[$path])) {
            return parent::beforeSave();
        }

        try {
            // The value submitted by the form field. Use null coalescing to ensure it's always set.
            $value = $this->getValue() ?? '';

            // Check if the value is an array, which is a common scenario for file uploads
            // or other complex field types. We need to extract the correct string value.
            if (is_array($value)) {
                if (isset($value[0])) {
                    // This is for some multi-select fields.
                    $value = $value[0];
                } elseif (isset($value['name'])) {
                    // This is for a new file upload.
                    $value = $value['name'];
                } elseif (isset($value['value'])) {
                    // This is for an existing file.
                    $value = $value['value'];
                } elseif (isset($value['delete']) && $value['delete'] == 1) {
                    // This is for when a file is deleted.
                    $value = '';
                } else {
                    // If no valid key is found, set value to an empty string.
                    $value = '';
                }
            }

            $attributeCode = $map[$path];

            // Load the vendor model using its ID.
            $vendor = $this->vendorFactory->create()->load($vendorId);

            // If the vendor model couldn't be loaded, we can't proceed.
            if ($vendor->getId()) {
                // Set the EAV attribute data on the vendor model.
                // The model's resource will automatically handle the insert/update logic.
                $vendor->setData($attributeCode, $value);

                // Save the vendor model.
                $vendor->save();
            }

            // CRITICAL FIX: Remove the value data entirely to prevent the
            // Vnecoms module from trying to save the array to the database.
            // This is the most reliable way to bypass the "Array to string conversion" error.
            $this->unsetData('value');

        } catch (\Exception $e) {
            // Log any errors that occur during the sync process.
            //$this->_logger->error('Vendor EAV sync failed: ' . $e->getMessage());
        }
        
        // It is important to call the parent method to ensure the default Magento config saving logic still runs.
        return parent::beforeSave();
    }
}
