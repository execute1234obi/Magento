<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Service\VerificationUpdateService;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vnecoms\Vendors\Model\Vendor;

class VendorInfoSaveBefore implements ObserverInterface
{
    protected ManagerInterface $messageManager;
    protected VendorVerificationFactory $vendorVerificationFactory;
    protected VerificationInfoFactory $verificationInfoFactory;
    protected VerificationUpdateService $verificationUpdateService;

    public function __construct(
        ManagerInterface $messageManager,
        VendorVerificationFactory $vendorVerificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        VerificationUpdateService $verificationUpdateService
    ) {
        $this->messageManager = $messageManager;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->verificationUpdateService = $verificationUpdateService;
    }

    public function execute(Observer $observer)
{
    $vendor = $observer->getEvent()->getObject();

    if (!$vendor instanceof Vendor || !$vendor->hasDataChanges()) {
        return;
    }

    $verification = $this->vendorVerificationFactory
        ->create()
        ->load((int)$vendor->getId(), 'vendor_id');

    if (!$verification->getId()) {
        return;
    }

    if ((int)$verification->getIsVerified()) {
        $this->messageManager->addErrorMessage(
            __('You cannot update vendor information after verification.')
        );
        $vendor->setData($vendor->getOrigData());
        return;
    }

    $map = [
        'b_name'                => 'business-name',
        'business_descriptions' => 'business-description',
        'business_type'         => 'business-type',
        'website'               => 'business-website',
        'country_id'            => 'country_id',
        'b_ph'                  => 'business-phone',
        'b_email'               => 'business-email'
    ];

    $data = [];
    foreach ($map as $field => $key) {
        if (($vendor->getData($field)) != ($vendor->getOrigData($field))) {
            $data[$key] = $vendor->getData($field);
        }
    }

    if (!$data) {
        return;
    }

    // 👇 Observer sirf service call kare
    $this->verificationUpdateService->update(
        $vendor,
        (int)$verification->getId(),
        InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION,
        0, // 👈 detail_id service handle karegi
        $data,
        true
    );
}

}
