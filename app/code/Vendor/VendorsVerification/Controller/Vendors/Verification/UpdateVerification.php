<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Helper\Data as VerificationHelper;
use Vnecoms\Vendors\Model\VendorFactory;
use Magento\Framework\App\ResourceConnection;

class UpdateVerification extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    protected $vendorSession;
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $helper;
    protected $messageManager;
    protected $vendorFactory;
    protected $vendorResource;

    public function __construct(
      Context $context,                                     // 1
        VendorSession $vendorSession,                         // 2
        VendorVerificationFactory $verificationFactory,       // 3
        VerificationInfoFactory $verificationInfoFactory,     // 4
        VerificationHelper $helper,                           // 5
        VendorFactory $vendorFactory,                         // 6
        \Vnecoms\Vendors\Model\ResourceModel\Vendor $vendorResource // 7 (Add this if not there)
    ) {
        parent::__construct($context);
        $this->vendorSession = $vendorSession;
        $this->verificationFactory = $verificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->helper = $helper;
        $this->vendorFactory = $vendorFactory;
        $this->vendorResource = $vendorResource; // Assignment
        $this->messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        try {
            // FIX 1: Corrected variable from $sessionVendor to $vendor
            $vendorSessionModel = $this->vendorSession->getVendor();
            $vendorId = (int)$vendorSessionModel->getId();

            if (!$vendorId) {
                throw new \Exception(__('Vendor session expired.'));
            }

            /** 🔥 MASTER VENDOR MODEL (Fresh load for sync) */
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $data = $this->getRequest()->getParams();

            $verificationId = (int)($data['id'] ?? 0);
            $typeId         = (int)($data['typ_id'] ?? 0);
            $detailId = (int)$this->getRequest()->getParam('dtl_id');

            //echo($verificationId);
            //exit();

            if (!$verificationId || !$typeId) {
                throw new \Exception(__('Invalid request parameters.'));
            }

            $verification = $this->verificationFactory->create()->load($verificationId);
            
           /** ================= VERIFICATION INFO ROW LOAD ================= */
// Pehle URL se dtl_id lene ki koshish karein
$detailId = (int)$this->getRequest()->getParam('dtl_id');
$verificationInfo = $this->verificationInfoFactory->create();


if ($detailId) {
    // Agar detail_id mil raha hai toh direct load karein (Force Update)
    $verificationInfo->load($detailId);
}

// Agar load nahi hua (naya record) ya detail_id nahi tha, tab purana tarika use karein
if (!$verificationInfo->getId()) {
    $verificationInfo = $verificationInfo->getCollection()
        ->addFieldToFilter('verification_id', $verificationId)
        ->addFieldToFilter('datagroup_id', $typeId)
        ->getFirstItem();
}

            /* ================= SECURITY CHECKS ================= */
            if ($verification->getVendorId() != $vendorId) {
                throw new \Exception(__('Invalid verification access.'));
            }

            // Note: If saving fails here, ensure the status in DB is actually 'resubmit'
            if ($verificationInfo->getStatus() != Status::VENDOR_VERIFICATION_STATUS__RESUBMIT) {
                throw new \Exception(__('This section is not allowed to update. Current status: '.$verificationInfo->getStatus()));
            }

            /* ================= DATA PROCESSING & SYNC ================= */
            $processedData = [];

            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION) {
                $processedData = [
                    'business-name' => trim($data['business-name'] ?? ''),
                    'business-description' => trim($data['business-description'] ?? ''),
                    'business-type' => $data['business-type'] ?? null,
                    'business-website' => trim($data['business-website'] ?? ''),
                    'country_id' => $data['country_id'] ?? null,
                    'business-phone' => trim($data['business-phone'] ?? ''),
                    'business-email' => trim($data['business-email'] ?? '')
                ];
            }

            $oldData = [];

            if ($verificationInfo->getId() && $verificationInfo->getVendorData()) {
                $oldData = json_decode($verificationInfo->getVendorData(), true) ?? [];
            }
            //print_r($processedData);exit();
            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS) {
                $processedData = [
                    'street'   => trim($data['street'] ?? ''),
                    'city'     => trim($data['city'] ?? ''),
                    'state'    => trim($data['state'] ?? ''),
                    'postcode' => trim($data['postcode'] ?? ''),
                    'map'      => trim($data['map'] ?? '')
                ];
            }

            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT) {
                $processedData = [
                    'contact_name'  => trim($data['contact_name'] ?? ''),
                    'contact_phone' => trim($data['contact_phone'] ?? ''),
                    'contact_email' => trim($data['contact_email'] ?? ''),
                    'country_code'  => trim($data['country_code'] ?? '')
                ];
            }

            // if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {
            //     $processedData = [
            //         'certificate' => $data['vendorregistcert'][0] ?? '',
            //         'documents'   => $data['vendoradditionaldocs'] ?? ''
            //     ];
            // }
//            if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {
//     // Frontend JS se 'certificate' aur 'documents' naam se data aa raha hai
//     $certFile = $data['certificate'] ?? ''; 
//     $additionalDocs = $data['documents'] ?? '';

//     // File names nikalne ke liye logic
//     $certName = is_array($certFile) ? ($certFile[0]['name'] ?? '') : $certFile;
    
//     // Multiple documents ko handle karne ke liye
//     $docNames = [];
//     if (is_array($additionalDocs)) {
//         foreach ($additionalDocs as $doc) {
//             $docNames[] = $doc['name'] ?? $doc;
//         }
//     } else {
//         $docNames = $additionalDocs;
//     }

//     $processedData = [
//         'certificate' => $certName,
//         'documents'   => is_array($docNames) ? implode(',', $docNames) : $docNames
//     ];
// }
if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {
   
    $certFile = $this->getRequest()->getParam('certificate'); 
    $docsFile = $this->getRequest()->getParam('documents');

//    echo '<pre>';
//print_r($this->getRequest()->getParams());
//print_r($oldData);
//echo $certFile;
//echo $docsFile;
//die;


    // Helper use karke clean names nikalna
    $certName = $this->getFileName($certFile); // Single file
    $docNames = $this->getFileName($docsFile, true); // Multiple files
    $docNames = trim($docNames, ',');

  
    //echo '<pre>'; print_r($docsFile);
     // 🔥 FOLDER PREFIX
    $folderPrefix = 'vendor-verification-documents/';
//     $processedData = [
//     'certificate' => $certName ? $folderPrefix.$certName : '',
//     'documents'   => $docNames ? $folderPrefix.$docNames : ''
// ];
$processedData = [];

if (!empty($certFile)) {
    $processedData['certificate'] = $folderPrefix.$certName;
}
else
{
    $processedData['certificate'] = $oldData['certificate'];
}

if (!empty($docsFile)) {
    $processedData['documents'] = $folderPrefix.$docNames;
}
else
{
     $processedData['documents'] = $oldData['documents'];
}

//print_r($processedData);
//exit();
}


            // Save and Sync if data exists
           $shouldSave = true;

if ($typeId == InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS) {
    $shouldSave = $this->hasCertDocsChanged($oldData, $processedData);
}
//print_r($oldData);
//print_r($processedData);
//die();
if ($shouldSave && !empty($processedData)) {

    $this->saveInfoGroup(
        $verificationInfo,
        $processedData,
        $verificationId,
        $typeId
    );

    $this->syncVendorFromVerification(
        $vendor,
        $typeId,
        $processedData
    );

    // 🔥 observer flags
    $vendor->setData('verification_sync_group_id', $typeId);
    $vendor->setData('skip_full_vendor_sync', 1);

    $this->vendorResource->save($vendor);
}


            $this->messageManager->addSuccess(__('Verification data updated and synced successfully.'));
        } catch (\Exception $e) {
    // Agar error "Unable to send mail" hai, to use ignore karein ya simple message dikhayein
    if (strpos($e->getMessage(), 'mail') !== false) {
        $this->messageManager->addSuccess(__('Data updated, but email could not be sent.'));
    } else {
        $this->messageManager->addError(__('Something went wrong: ') . $e->getMessage());
    }
}
        // FIX 2: Ensuring proper redirect back to index
        return $this->_redirect('vendorverification/verification/index');
    }

    // private function saveInfoGroup($verificationInfo, array $data,$verificationId, $typeId)
    // {

    //     if (!$verificationInfo->getId()) {
    //     $verificationInfo->setData('verification_id', $verificationId);
    //     $verificationInfo->setData('datagroup_id', $typeId);
    // }
    //     $verificationInfo->setVendorData($this->helper->arrayToJson($data));
    //     $verificationInfo->setApproval(0);
    //     $verificationInfo->setStatus(Status::VENDOR_VERIFICATION_STATUS_PENDING);
    //     $verificationInfo->getResource()->save($verificationInfo);
    // }
private function saveInfoGroup($verificationInfo, array $data, $verificationId, $typeId)
{
    // Model mein data set karein
    $verificationInfo->setData('verification_id', (int)$verificationId);
    $verificationInfo->setData('datagroup_id', (int)$typeId);
    $verificationInfo->setData('vendor_data', $this->helper->arrayToJson($data));
    $verificationInfo->setData('approval', 0);
    $verificationInfo->setData('status', Status::VENDOR_VERIFICATION_STATUS_PENDING);

    // SQL query mein batana padta hai ki ye data "changed" hai
    $verificationInfo->setHasDataChanges(true);

    // 🔥 Model->save() ki jagah Resource Model ka use karein
    // Isse query mein verification_id aur datagroup_id dono jayenge
    $verificationInfo->getResource()->save($verificationInfo);
}

    private function syncVendorFromVerification($vendor, $typeId, array $data)
    {
        $vendor->setHasDataChanges(true);
        switch ($typeId) {
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION:
              $vendor->setData('b_name', $data['business-name'] ?? null);
            $vendor->setData('business_descriptions', $data['business-description'] ?? null);
            $vendor->setData('business_type', $data['business-type'] ?? null);
            $vendor->setData('website', $data['business-website'] ?? null);
            
            // NOTE: Agar country_id master table mein blank hai, 
            // toh ho sakta hai ye vendor address table se connect ho. 
            $vendor->setData('country_id', $data['country_id'] ?? null);
            
            $vendor->setData('b_ph', $data['business-phone'] ?? null);
            $vendor->setData('b_email', $data['business-email'] ?? null);
            break;
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT:
                $vendor->setData('c_name', $data['contact_name'] ?? null);
                $vendor->setData('contact_phone', $data['contact_phone'] ?? null);
                $vendor->setData('contact_email', $data['contact_email'] ?? null);
                $vendor->setData('country_code', $data['country_code'] ?? null);
                break;
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS:
                $vendor->setData('street', $data['street'] ?? null);
                $vendor->setData('city', $data['city'] ?? null);
                $vendor->setData('region', $data['state'] ?? null); // Matches DB ID 152
                $vendor->setData('postcode', $data['postcode'] ?? null);
                $vendor->setData('map', $data['map'] ?? null);
                break;
            case InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS:
                // if (!empty($data['certificate'])) {
                //     $vendor->setData('certificate', $data['certificate']);
                // }
                if (!empty($data['certificate'])) {
        $vendor->setData('certificate', $data['certificate']);
    }

    if (!empty($data['documents'])) {
        $vendor->setData('documents', $data['documents']);
    }

                break;
        }
    }
    /**
     * Helper function to extract file name from UI component array structure
     * * @param array|string $fileData
     * @param bool $isMultiple
     * @return string
     */
   /**
 * UI Component ke array se file ka naam nikalne ke liye
 */
// private function getFileName($fileData, $isMultiple = false) 
// {
//     if (empty($fileData)) {
//         return '';
//     }

//     // Agar data array hai (KnockoutJS format)
//     if (is_array($fileData)) {
//         if ($isMultiple) {
//             $names = [];
//             foreach ($fileData as $file) {
//                 // Pehle 'file' key check karein, phir 'name'
//                 $names[] = $file['file'] ?? ($file['name'] ?? '');
//             }
//             return implode(',', array_filter($names));
//         }

//         // Single file ke liye
//         $firstFile = reset($fileData);
        
//         return $firstFile['file'] ?? ($firstFile['name'] ?? '');
//     }

//     return (string)$fileData;
// }

private function getFileName($fileData, $isMultiple = false)
{
    if (empty($fileData)) {
        return '';
    }

    if (is_array($fileData)) {

        // 🔥 MULTIPLE FILES
        if ($isMultiple) {
            $names = [];

            foreach ($fileData as $file) {
                $name = '';

                if (is_array($file)) {
                    $name = $file['file'] ?? ($file['name'] ?? '');
                } else {
                    $name = (string)$file;
                }

                $name = trim($name);

                // ❗ Empty values skip karo
                if ($name !== '') {
                    $names[] = $name;
                }
            }

            return implode(',', $names);
        }

        // 🔥 SINGLE FILE
        $first = reset($fileData);
        return is_array($first)
            ? ($first['file'] ?? ($first['name'] ?? ''))
            : (string)$first;
    }

    return trim((string)$fileData);
}
private function hasCertDocsChanged(array $oldData, array $newData): bool
{
    $oldCert = trim((string)($oldData['certificate'] ?? ''));
    $oldDocs = trim((string)($oldData['documents'] ?? ''));

    $newCert = trim((string)($newData['certificate'] ?? ''));
    $newDocs = trim((string)($newData['documents'] ?? ''));

    // 🧠 Empty new value ka matlab "no change"
    if ($newCert === '') {
        $newCert = $oldCert;
    }

    if ($newDocs === '') {
        $newDocs = $oldDocs;
    }

    return ($oldCert !== $newCert) || ($oldDocs !== $newDocs);
}

}