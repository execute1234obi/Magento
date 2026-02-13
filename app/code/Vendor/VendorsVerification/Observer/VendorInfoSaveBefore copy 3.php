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
    protected $messageManager;
    protected $vendorVerificationFactory;
    protected $verificationInfoFactory;

    /** @var VerificationUpdateService */
    private VerificationUpdateService $verificationUpdateService;

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
        $model = $observer->getEvent()->getObject();

        /* ✔️ Only Vendor model */
        if (!$model instanceof Vendor) {
            return;
        }

        $vendorId = (int) $model->getId();
        if (!$vendorId) {
            return;
        }

        /* 🔹 Load vendor verification */
        $verification = $this->vendorVerificationFactory
            ->create()
            ->load($vendorId, 'vendor_id');

        if (!$verification->getId()) {
            return;
        }

        $verificationId = (int) $verification->getId();
        $isVerified     = (int) $verification->getIsVerified();

        /* 🔴 Verified vendor → restrict */
        if ($isVerified && $model->hasDataChanges()) {
            $this->messageManager->addErrorMessage(
                __('You cannot update vendor information after verification. Please contact admin if changes are required.')
            );
            $model->setData($model->getOrigData());
            return;
        }

        /* 🟢 Non-verified vendor → sync Group-1 */
        if (!$isVerified && $model->hasDataChanges()) {

            /* Group-1 vendor fields */
            $group1Fields = [
                'b_name',
                'business_type',
                'business_descriptions',
                'website',
                'country_id',
                'b_ph',
                'b_email'
            ];

            /* Vendor → verification mapping */
            $group1Map = [
                'b_name'                => 'business-name',
                'business_descriptions' => 'business-description',
                'business_type'         => 'business-type',
                'website'               => 'business-website',
                'country_id'            => 'country_id',
                'b_ph'                  => 'business-phone',
                'b_email'               => 'business-email'
            ];

            $newData = $model->getData();
            $oldData = $model->getOrigData();

            $businessData = [];

            foreach ($group1Fields as $field) {
                $newValue = $newData[$field] ?? null;
                $oldValue = $oldData[$field] ?? null;

                if ($newValue != $oldValue && isset($group1Map[$field])) {
                    $businessData[$group1Map[$field]] = $newValue;
                }
            }

            if (empty($businessData)) {
                return;
            }

            /* 🔹 Load / create verification_info (Group-1) */
            $verificationInfo = $this->verificationInfoFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('verification_id', $verificationId)
                ->addFieldToFilter(
                    'type_id',
                    InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION
                )
                ->getFirstItem();

            if (!$verificationInfo->getId()) {
                $verificationInfo->setVerificationId($verificationId);
                $verificationInfo->setTypeId(
                    InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION
                );
                $verificationInfo->save();
            }

            $detailId = (int) $verificationInfo->getId();

            /* 🔹 Call service */
            $this->verificationUpdateService->update(
                $model,
                $verificationId,
                InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION,
                $detailId,
                $businessData,
                true
            );
        }
    }
}
