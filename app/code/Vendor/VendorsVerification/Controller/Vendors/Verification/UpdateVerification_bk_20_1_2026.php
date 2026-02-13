<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Helper\Data as VerificationHelper;
use Magento\Framework\App\ResourceConnection;

class UpdateVerification extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    protected $resourceConnection;
    protected $vendorSession;
    protected $vendorFactory;
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $helper;

    public function __construct(
        Context $context,
        VendorSession $vendorSession,
        VendorVerificationFactory $verificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        VerificationHelper $helper,
        ResourceConnection $resourceConnection,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
    ) {
        parent::__construct($context);
        $this->vendorSession = $vendorSession;
        $this->verificationFactory = $verificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->helper = $helper;
        $this->resourceConnection = $resourceConnection;
        $this->vendorFactory = $vendorFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        //echo '<pre>';
        //print_r($data);
        //exit;
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $vendorSess = $this->vendorSession->getVendor();
            $vendorId = $vendorSess->getId();

            // Load fresh Vendor model
            $vendor = $this->vendorFactory->create()->load($vendorId);
            
            if (!$vendor->getId()) {
                throw new \Exception(__('Vendor not found.'));
            }

            $verificationId = (int)($data['id'] ?? 0);
            $typeId         = (int)($data['typ_id'] ?? 0);
            $detailId       = (int)($data['dtl_id'] ?? 0);

            if (!$verificationId || !$typeId || !$detailId) {
                throw new \Exception(__('Invalid request.'));
            }

            $verification = $this->verificationFactory->create()->load($verificationId);
            //$verificationInfo = $this->verificationInfoFactory->create()->load($detailId);

            $verificationInfo = $this->verificationInfoFactory->create()
    ->getCollection()
    ->addFieldToFilter('verification_id', $verificationId)
    ->addFieldToFilter('datagroup_id', $typeId)
    ->getFirstItem();

if (!$verificationInfo->getId()) {
    throw new \Exception(__('Verification detail row not found for update.'));
}


            if ($verification->getVendorId() != $vendorId) {
                throw new \Exception(__('Invalid verification access.'));
            }

            // ... (Your data mapping logic here) ...
            $groupData = $this->getGroupData($typeId, $data);

            /* SAVE VERIFICATION INFO */
            $verificationInfo->setVendorData($this->helper->arrayToJson($groupData));
            $verificationInfo->setApproval(0);
            $verificationInfo->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
            $verificationInfo->save();

            /* SYNC VENDOR TABLE */
            $this->syncVendorFromVerification($vendor, $typeId, $groupData);
            
            $vendor->save();
            $connection->commit();

            $this->messageManager->addSuccess(__('Verification data updated successfully.'));
            return $this->_redirect('vendorverification/verification/index');

        } catch (\Exception $e) {
            $connection->rollBack();
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('vendorverification/verification/index');
        }
    }

    private function getGroupData($typeId, $data) {
        if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION) {
            return [
                'business-name' => trim($data['business-name'] ?? ''),
                'business-description' => trim($data['business-description'] ?? ''),
                'business-type' => $data['business-type'] ?? null,
                'business-website' => trim($data['business-website'] ?? ''),
                'country_id' => $data['country_id'] ?? null,
                'business-phone' => trim($data['business-phone'] ?? ''),
                'business-email' => trim($data['business-email'] ?? '')
            ];
        }
        /** ✅ BUSINESS CONTACT GROUP */
    if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT) {
        return [
            'contact_name'  => trim($data['contact_name'] ?? ''),
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'contact_email' => trim($data['contact_email'] ?? ''),
            'country_code'  => trim($data['country_code'] ?? '')
        ];
    }
       /** ================= BUSINESS ADDRESS ✅ ================= */
    if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS) {
        return [
            'street'   => trim($data['street'] ?? ''),
            'city'     => trim($data['city'] ?? ''),
            'state'    => trim($data['state'] ?? ''),
            'postcode' => trim($data['postcode'] ?? ''),
            'map'      => trim($data['map'] ?? '')
        ];
    }
    if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {

    $cert = null;
    if (isset($data['certificate'][0]['name'])) {
        $cert = $data['certificate'][0]['name'];
    }

    $docs = [];
    if (!empty($data['documents'])) {
        foreach ($data['documents'] as $doc) {
            if (isset($doc['name'])) {
                $docs[] = $doc['name'];
            }
        }
    }

    return [
        'certificate' => $cert,
        'documents'   => implode(',', $docs)
    ];
}

        return [];
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