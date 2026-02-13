<?php
namespace Vendor\CustomConfig\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Vnecoms\VendorsConfig\Model\ConfigFactory;
use Psr\Log\LoggerInterface;

class SyncVendorAttributeToConfig implements ObserverInterface
{
    protected $resource;
    protected $configFactory;
    protected $logger;

    public function __construct(
        ResourceConnection $resource,
        ConfigFactory $configFactory,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->configFactory = $configFactory;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->debug('=== Observer Triggered: SyncVendorAttributeToConfig ===');

        $vendor = $observer->getEvent()->getVendor();
        if (!$vendor || !$vendor->getId()) {
            $this->logger->debug('No vendor found');
            return;
        }

        $vendorId = $vendor->getId();
        $this->logger->debug('Vendor ID: ' . $vendorId);

        $connection = $this->resource->getConnection();

        $varcharTable = $this->resource->getTableName('ves_vendor_entity_varchar');
        $textTable = $this->resource->getTableName('ves_vendor_entity_text');

        // BUSINESS NAME (attribute_id = 174)
        $selectName = $connection->select()
            ->from($varcharTable, ['value'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', 174);
        $businessName = $connection->fetchOne($selectName);

        $this->logger->debug('Business Name: ' . $businessName);

        // BUSINESS DESCRIPTION (attribute_id = 177)
        $selectDesc = $connection->select()
            ->from($textTable, ['value'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', 177);
        $businessDescription = $connection->fetchOne($selectDesc);

        $this->logger->debug('Business Description: ' . $businessDescription);

        // SAVE TO CONFIG
        if ($businessName) {
            $configModel = $this->configFactory->create();
            $configModel->setVendorId($vendorId)
                ->setPath('vendor/business/name')
                ->setValue($businessName)
                ->save();
        }

        if ($businessDescription) {
            $configModel = $this->configFactory->create();
            $configModel->setVendorId($vendorId)
                ->setPath('vendor/business/description')
                ->setValue($businessDescription)
                ->save();
        }

        $this->logger->debug('=== Sync Completed ===');
    }
}
