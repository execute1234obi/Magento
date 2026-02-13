<?php

namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vendor\VendorsVerification\Service\VendorInfoSyncService;

class VendorInfoSaveBefore implements ObserverInterface
{
    /**
     * @var VendorInfoSyncService
     */
    protected $syncService;

    public function __construct(
        VendorInfoSyncService $syncService
    ) {
        $this->syncService = $syncService;
    }

    /**
     * Before Vendor Information Save
     */
    public function execute(Observer $observer)
    {
        /**
         * Vnecoms vendor model
         * Event usually sends `vendor`
         */
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);

        $logger->info('VendorInfoSaveBefore observer CALLED');

        $vendor = $observer->getEvent()->getVendor();

        if (!$vendor || !$vendor->getId()) {
            return;
        }

        /**
         * Convert vendor object to array
         * (same data you pasted in debug)
         */
        $vendorData = $vendor->getData();

        /**
         * Sync ONLY update
         * Status = 5 excluded inside service
         */
        $this->syncService->sync($vendorData);
    }
}
