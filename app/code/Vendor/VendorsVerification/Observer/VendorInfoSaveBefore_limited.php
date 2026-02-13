<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;

class VendorInfoSaveBefore implements ObserverInterface
{
    protected $messageManager;
    protected $vendorVerificationFactory;

    public function __construct(
        ManagerInterface $messageManager,
        VendorVerificationFactory $vendorVerificationFactory
    ) {
        $this->messageManager = $messageManager;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
    }

    public function execute(Observer $observer)
    {
        //echo("hi me aa gayi");
        //exit();
        $model = $observer->getEvent()->getObject();

        // ✔️ Sirf vendor model par apply ho
        if (!$model instanceof \Vnecoms\Vendors\Model\Vendor) {
            return;
        }

        $vendorId = (int)$model->getId();

        if (!$vendorId) {
            return;
        }

        /** Vendor Verification Data */
        // $verification = $this->vendorVerificationFactory->create()
        //     ->load($vendorId, 'vendor_id');

        $verification = $this->vendorVerificationFactory->create()
    ->getCollection()
    ->addFieldToFilter('vendor_id', $vendorId)
    ->addFieldToFilter('status', ['neq' => 5])
    ->setOrder('created_at', 'DESC');
        //echo '<pre>';
        //print_r($verification->getData());
        //die;

        if (!$verification->getId()) {
            return;
        }

        $isVerified = (int)$verification->getIsVerified(); // 1 = verified

        // 🔴 Agar vendor verified hai aur data change hua
      if ($isVerified && $model->hasDataChanges()) {

   $this->messageManager->addErrorMessage(
        __('You cannot update vendor information after verification. Please contact admin if changes are required.')
    );

    $model->setData($model->getOrigData());
    return;
}


        // 🟢 Agar vendor verified nahi hai → data sync allow
        if (!$isVerified && $model->hasDataChanges()) {
            
            //echo("data sync ho sakta he");
            //exit;
            // yaha aap vendor verification table me data sync kar sakti ho
        }
    }
}
