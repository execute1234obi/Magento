<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Helper\Data as VerificationHelper;
use Vnecoms\Vendors\Model\VendorFactory; //vendor sync

class UpdateVerification extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    protected $vendorSession;
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $helper;
    protected $messageManager;
    protected $vendorFactory; //vendor sync

    public function __construct(
      Context $context,
    VendorSession $vendorSession,
    VendorVerificationFactory $verificationFactory,
    VerificationInfoFactory $verificationInfoFactory,
    VerificationHelper $helper,
    \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
) {
    parent::__construct($context);

    $this->vendorSession = $vendorSession;
    $this->verificationFactory = $verificationFactory;
    $this->verificationInfoFactory = $verificationInfoFactory;
    $this->helper = $helper;
    $this->vendorFactory = $vendorFactory;
    $this->messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        //echo 'UpdateVerification controller HIT';
        $data = $this->getRequest()->getParams();
        //echo '<pre>';
        //print_r($data);
        //exit;
        try {
            $vendor = $this->vendorSession->getVendor();
            //$vendorId = $vendor->getId();

           $vendorId = (int)$sessionVendor->getId();

        if (!$vendorId) {
            throw new \Exception(__('Vendor session expired.'));
        }

            /** 🔥 MASTER VENDOR MODEL (for save) */
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $data = $this->getRequest()->getParams();

            $verificationId = (int)($data['id'] ?? 0);
            $typeId         = (int)($data['typ_id'] ?? 0);
            $detailId       = (int)($data['dtl_id'] ?? 0);

            if (!$verificationId || !$typeId || !$detailId) {
                throw new \Exception(__('Invalid request.'));
            }

            $verification = $this->verificationFactory->create()->load($verificationId);
            //$verificationInfo = $this->verificationInfoFactory->create()->load($detailId);
            /** ================= VERIFICATION INFO ROW (SAFE LOAD) ================= */
        $verificationInfo = $this->verificationInfoFactory->create()
            ->getCollection()
            ->addFieldToFilter('verification_id', $verificationId)
            ->addFieldToFilter('datagroup_id', $typeId)
            ->getFirstItem();

        if (!$verificationInfo->getId()) {
            throw new \Exception(__('Verification detail row not found.'));
        }

            /* ================= SECURITY CHECKS ================= */

            if ($verification->getVendorId() != $vendorId) {
                throw new \Exception(__('Invalid verification access.'));
            }

            if ($verification->getIsVerified() == 2) {
                throw new \Exception(__('Verification already completed.'));
            }

            if ($verificationInfo->getStatus() != Status::VENDOR_VERIFICATION_STATUS__RESUBMIT) {
                throw new \Exception(__('This section is not allowed to update.'));
            }

            /* ================= BUSINESS INFORMATION ================= */
          if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION) {

    $vendorInfo = [
    'business-name' => trim($data['business-name'] ?? ''),
                'business-description' => trim($data['business-description'] ?? ''),
                'business-type' => $data['business-type'] ?? null,
                'business-website' => trim($data['business-website'] ?? ''),
                'country_id' => $data['country_id'] ?? null,
                'business-phone' => trim($data['business-phone'] ?? ''),
                'business-email' => trim($data['business-email'] ?? '')
];

    $this->saveInfoGroup($verificationInfo, $vendorInfo);
    $this->syncVendorFromVerification($vendor, $typeId, $vendorInfo);

}

            /* ================= BUSINESS ADDRESS ================= */
            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS) {

               $vendorAddress = [
       'street'   => trim($data['street'] ?? ''),
            'city'     => trim($data['city'] ?? ''),
            'state'    => trim($data['state'] ?? ''),
            'postcode' => trim($data['postcode'] ?? ''),
            'map'      => trim($data['map'] ?? '')
    ];
                $this->saveInfoGroup($verificationInfo, $vendorAddress);
                $this->syncVendorFromVerification($vendor, $typeId, $vendorAddress);

            }

            /* ================= BUSINESS CONTACT ================= */
            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT) {

                $vendorContact = [
                  'contact_name'  => trim($data['contact_name'] ?? ''),
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'contact_email' => trim($data['contact_email'] ?? ''),
            'country_code'  => trim($data['country_code'] ?? '')
                ];

                $this->saveInfoGroup($verificationInfo, $vendorContact);
                $this->syncVendorFromVerification($vendor, $typeId, $vendorContact);

            }

            /* ================= CERTIFICATES ================= */
            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {

                $docs = [
                    'certificate' => $data['vendorregistcert'][0] ?? '',
                    'documents'   => $data['vendoradditionaldocs'] ?? ''
                ];

                $this->saveInfoGroup($verificationInfo, $docs);
                $this->syncVendorFromVerification($vendor, $typeId, $docs);

            }
            $vendor->save();
            $this->messageManager->addSuccess(__('Verification data updated successfully.'));
            return $this->_redirect('vendorverification/verification/index');

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('vendorverification/verification/index');
        }
    }

    /**
     * Save common verification info group
     */
    private function saveInfoGroup($verificationInfo, array $data)
    {
        //print_r($verificationInfo);
        //exit();
        $verificationInfo->setVendorData($this->helper->arrayToJson($data));
        $verificationInfo->setApproval(0);
        $verificationInfo->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
        $verificationInfo->save();
    }

     private function syncVendorFromVerification($vendor, $typeId, array $data)
    {
        switch ($typeId) {
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION:
                $vendor->setData('b_name', $data['business-name'] ?? null);
                $vendor->setData('business_descriptions', $data['business-description'] ?? null);
                $vendor->setData('business_type', $data['business-type'] ?? null);
                $vendor->setData('website', $data['business-website'] ?? null);
                $vendor->setData('country_id', $data['country_id'] ?? null);
                $vendor->setData('b_ph', $data['business-phone'] ?? null);
                $vendor->setData('b_email', $data['business-email'] ?? null);
                break;
             /** ✅ BUSINESS CONTACT SYNC */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT:
            $vendor->setData('c_name', $data['contact_name'] ?? null);
            $vendor->setData('contact_phone', $data['contact_phone'] ?? null);
            $vendor->setData('contact_email', $data['contact_email'] ?? null);
            $vendor->setData('country_code', $data['country_code'] ?? null);
            break;
              /** ================= BUSINESS ADDRESS ✅ ================= */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS:
            $vendor->setData('street', $data['street'] ?? null);
            $vendor->setData('city', $data['city'] ?? null);
            $vendor->setData('region', $data['state'] ?? null);
            $vendor->setData('postcode', $data['postcode'] ?? null);
            $vendor->setData('map', $data['map'] ?? null);
            break;
         /** ================= CERTIFICATES & DOCUMENTS ✅ ================= */
        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS:

            // Single certificate
            if (!empty($data['certificate'])) {
                $vendor->setData('registration_certificate', $data['certificate']);
            }

            // Multiple documents (comma separated)
            if (!empty($data['documents'])) {
                $vendor->setData('additional_documents', $data['documents']);
            }

            break;
        }
        
    }
}
