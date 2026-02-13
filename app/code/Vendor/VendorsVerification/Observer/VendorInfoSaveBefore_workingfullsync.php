<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Service\VendorInfoSyncService;
use Vendor\VendorsVerification\Model\Source\Status;
use Vnecoms\Vendors\Model\Vendor;

class VendorInfoSaveBefore implements ObserverInterface
{
    protected $messageManager;
    protected $vendorVerificationFactory;
    protected $syncService;
    protected $logger;

    public function __construct(
        ManagerInterface $messageManager,
        VendorVerificationFactory $vendorVerificationFactory,
        VendorInfoSyncService $syncService,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
        $this->syncService = $syncService;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('VendorInfoSaveBefore observer CALLED');

        $model = $observer->getEvent()->getObject();

        // ✅ Only Vendor model
        if (!$model instanceof Vendor) {
            return;
        }

        $vendorId = (int)$model->getId();
        if (!$vendorId) {
            return;
        }

        /**
         * ✅ Load latest verification MODEL (not collection)
         */
        $verification = $this->vendorVerificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
           ->addFieldToFilter('status', ['neq' => 5])
            ->setOrder('verification_id', 'DESC')
            ->getFirstItem();

        if (!$verification->getId()) {
            $this->logger->info('No verification found for vendor', ['vendor_id' => $vendorId]);
            return;
        }

        $isVerified = (int)$verification->getIsVerified();

        /**
         * 🔴 Verified vendor → block update
         */
        if ($isVerified && $model->hasDataChanges()) {
            $this->messageManager->addErrorMessage(
                __('You cannot update vendor information after verification. Please contact admin.')
            );

            // revert changes
            $model->setData($model->getOrigData());
            return;
        }

        /**
         * 🟢 Not verified → allow sync
         */
        if (!$isVerified && $model->hasDataChanges()) {
            $this->logger->info('Syncing vendor data', ['vendor_id' => $vendorId]);

            $vendorData = $model->getData(); // ✅ FIX
            $this->syncService->sync($vendorData);
        }
    }
}
