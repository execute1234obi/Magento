<?php
namespace Vendor\VendorsVerification\Service;

use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo\CollectionFactory as VerificationInfoCollectionFactory;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vendor\VendorsVerification\Helper\Data as JsonHelper;
use Psr\Log\LoggerInterface;


class VendorInfoSyncService
{
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $verificationInfoCollectionFactory;
    protected $jsonHelper;
    protected $logger;

    public function __construct(
        VendorVerificationFactory $verificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        VerificationInfoCollectionFactory $verificationInfoCollectionFactory,
        JsonHelper $jsonHelper,
        LoggerInterface $logger
    ) {
        $this->verificationFactory = $verificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->verificationInfoCollectionFactory = $verificationInfoCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    public function syncAll(array $vendorData): void
    {
        $vendorId = (int)($vendorData['entity_id'] ?? 0);
        //echo $vendorId ;
        //exit();
        if (!$vendorId) {
            $this->logger->info('SYNC STOPPED: vendor id missing');
            return;
        }

        $verification = $this->verificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', ['neq' => 5])
            ->setOrder('verification_id', 'DESC')
            ->getFirstItem();

        $certJson = $this->getCertificateAndDocsJson($vendorId);

   /// $certificate = $certJson['certificate'] ?? '';
    //$documents   = $certJson['documents'] ?? '';

//print_r($certificate);
//print_r($documents);

//die();
        if (!$verification->getId()) {
            $this->logger->info('SYNC STOPPED: no verification found', ['vendor_id' => $vendorId]);
            return;
        }

        $collection = $this->verificationInfoCollectionFactory->create()
            ->addFieldToFilter('verification_id', $verification->getId());

        foreach ($collection as $info) {
            $groupId = (int)$info->getDatagroupId();

            $jsonData = $this->buildGroupData($groupId, $vendorData, $certJson);

            if (empty($jsonData)) {
                continue;
            }

            $this->logger->info('SYNC DATA', [
                'group_id' => $groupId,
                'data' => $jsonData
            ]);

            $info->setVendorData($this->jsonHelper->arrayToJson($jsonData));
            $info->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
            $info->setApproval(0);
            $info->setUpdatedAt(date('Y-m-d H:i:s'));
            $info->save();
        }
    }

    private function buildGroupData(int $groupId, array $vendorData,array $existingCertJson = []): array
    {
        switch ($groupId) {
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION:
                return [
                    'business-name' => $vendorData['b_name'] ?? '',
                    'business-description' => $vendorData['business_descriptions'] ?? '',
                    'business-type' => $vendorData['business_type'] ?? '',
                    'business-website' => $vendorData['website'] ?? '',
                    'country_id' => $vendorData['country_id'] ?? '',
                    'business-phone' => $vendorData['b_ph'] ?? '',
                    'business-email' => $vendorData['b_email'] ?? '',
                ];

            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS:
                return [
                    'street' => $vendorData['street'] ?? '',
                    'city' => $vendorData['city'] ?? '',
                    'state' => $vendorData['b_state'] ?? '',
                    'postcode' => $vendorData['postcode'] ?? '',
                ];

            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT:
                return [
                    'contact_name' => $vendorData['c_name'] ?? '',
                    'contact_phone' => $vendorData['contact_phone'] ?? '',
                    'contact_email' => $vendorData['contact_email'] ?? '',
                    'country_code' => $vendorData['country_code'] ?? '',
                ];

    //        case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS:
    //              $certificateRaw = $vendorData['certificate'] ?? '';
    //              //print_r($certificateRaw);
    //              //die;

    //             $yourFolderName = 'vendor-verification-documents/';
    // // file attribute returns array
    //             if (is_array($certificateRaw)) {
    //                 $certificateRaw = $yourFolderName.$certificateRaw['value'] ?? '';
    //             }
    //             return [
    //                 'certificate' =>$certificateRaw ,
    //                 'documents' => $vendorData['additional_documents'] ?? '',
    //             ];
    case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS:
        
    $certificateRaw = $vendorData['certificate'] ?? '';

    // 🟢 If file attribute comes as array
    if (is_array($certificateRaw)) {
        $certificateRaw = $certificateRaw['value'] ?? '';
    }

    // 🟢 Ensure it's always string
    $certificateRaw = (string)$certificateRaw;
    // 🔥 additional documents (vendor info se kabhi nahi aayega)
    $documents = $existingCertJson['documents'] ?? '';


    return [
        'certificate' => $certificateRaw,
        'documents'   => $documents,
    ];

        }

        return [];
    }
    public function syncByGroup(
    int $verificationId,
    int $groupId,
    array $groupData
): void {
    $info = $this->verificationInfoCollectionFactory->create()
        ->addFieldToFilter('verification_id', $verificationId)
        ->addFieldToFilter('datagroup_id', $groupId)
        ->getFirstItem();

    if (!$info->getId()) {
        $this->logger->info('GROUP SYNC SKIPPED: row not found', [
            'verification_id' => $verificationId,
            'group_id' => $groupId
        ]);
        return;
    }

    $this->logger->info('GROUP SYNC', [
        'group_id' => $groupId,
        'data' => $groupData
    ]);

    $info->setVendorData(
        $this->jsonHelper->arrayToJson($groupData)
    );
    $info->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
    $info->setApproval(0);
    $info->setUpdatedAt(date('Y-m-d H:i:s'));
    $info->save();
}

public function getCertificateAndDocsJson(int $vendorId): array
{
    if (!$vendorId) {
        return [];
    }

    // latest verification
    $verification = $this->verificationFactory->create()
        ->getCollection()
        ->addFieldToFilter('vendor_id', $vendorId)
        ->setOrder('created_at', 'DESC')
        ->getFirstItem();

    if (!$verification->getId()) {
        return [];
    }

    // certificate & docs info row
    $info = $this->verificationInfoCollectionFactory->create()
        ->addFieldToFilter('verification_id', $verification->getId())
        ->addFieldToFilter(
            'datagroup_id',
            InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS
        )
        ->getFirstItem();

    if (!$info->getId()) {
        return [];
    }

    return json_decode($info->getVendorData(), true) ?? [];
}

}
