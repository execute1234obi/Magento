<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

class StoreDescription extends \Vnecoms\VendorsConfig\Model\Config
{
    protected $resource;
      protected $vendorSession;
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
        array $data = []
    ) {
        $this->resource = $resourceConnection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $configHelper, $serialize, $data);
    }

    public function afterSave()
    {
        $vendorId = $this->getVendorId(); // From parent class
        $value = $this->getValue();
        $attributeId = 177; // business_description attribute_id

        if (!$vendorId) {
            return parent::afterSave();
        }

        $connection = $this->resource->getConnection();

        // ✅ Use the correct table for "text" type attribute
        $tableName = $this->resource->getTableName('ves_vendor_entity_text');

        // Check if record already exists
        $select = $connection->select()
            ->from($tableName, ['value_id'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', $attributeId);

        $exists = $connection->fetchOne($select);

        // Update or insert accordingly
        if ($exists) {
            $connection->update(
                $tableName,
                ['value' => $value],
                ['entity_id = ?' => $vendorId, 'attribute_id = ?' => $attributeId]
            );
        } else {
            $connection->insert(
                $tableName,
                [
                    'entity_id' => $vendorId,
                    'attribute_id' => $attributeId,
                    'value' => $value
                ]
            );
        }

        return parent::afterSave();
    }
    /**
     * Load store description value for current vendor
     */
    public function afterLoad()
    {
       // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/config_debug.log');
        //$logger = new \Zend_Log();
        //$logger->addWriter($writer);
        //$logger->info('✅ afterLoad() called for Store Description');

        // Ensure vendor session
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->vendorSession) {
            $this->vendorSession = $objectManager->get(\Vnecoms\Vendors\Model\Session::class);
           // $logger->info('ℹ️ vendorSession loaded manually.');
        }

        $vendorId = null;

        if ($this->vendorSession && $this->vendorSession->getVendor()) {
            $vendorId = $this->vendorSession->getVendor()->getId();
            //$logger->info('Vendor ID from session: ' . $vendorId);
        } else {
            //$logger->info('⚠️ No vendor found in session.');
        }

        $attributeId = 177; // business_description attribute_id

        if ($vendorId) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('ves_vendor_entity_text');

            $select = $connection->select()
                ->from($tableName, ['value'])
                ->where('entity_id = ?', $vendorId)
                ->where('attribute_id = ?', $attributeId)
                ->limit(1);

            $value = $connection->fetchOne($select);
            //$logger->info('Loaded description from DB: ' . var_export($value, true));

            if ($value !== false) {
                $this->setValue($value);
            }
        } else {
            //$logger->info('⚠️ Vendor ID missing — cannot load store description.');
        }

        return parent::afterLoad();
    }
}
