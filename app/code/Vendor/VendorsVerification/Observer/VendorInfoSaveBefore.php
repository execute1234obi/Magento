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
//for file upload logic
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;


class VendorInfoSaveBefore implements ObserverInterface
{
    protected $messageManager;
    protected $vendorVerificationFactory;
    protected $syncService;
    protected $logger;

    //for file upload
    protected $uploaderFactory;
    protected $filesystem;


    public function __construct(
        ManagerInterface $messageManager,
        VendorVerificationFactory $vendorVerificationFactory,
        VendorInfoSyncService $syncService,
        LoggerInterface $logger,
        //for file upload
         UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        $this->messageManager = $messageManager;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
        $this->syncService = $syncService;
        $this->logger = $logger;
        //for file upload
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

   public function execute(Observer $observer)
{
    $vendor = $observer->getEvent()->getObject();

    

    if (!$vendor instanceof \Vnecoms\Vendors\Model\Vendor) {
        return;
    }

    // if (!$vendor->hasDataChanges()) {
    //     return;
    // }
    $hasFileUpload =
    isset($_FILES['vendor_data']['name']['certificate']) &&
    $_FILES['vendor_data']['name']['certificate'] !== '';


    //echo '<pre>';
//print_r($_FILES);
//print_r($hasFileUpload);
//die;
    if (!$vendor->hasDataChanges() && !$hasFileUpload) {
        return;
    }

    // 🔥 KEY CHECK
    if ($vendor->getData('skip_full_vendor_sync')) {
        // verification form se save hua → full sync nahi
        return;
    }
    // 🔑 Allow admin area updates
try {
    if (php_sapi_name() !== 'cli') {
        $areaCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\State::class)
            ->getAreaCode();

        if ($areaCode === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return;
        }
    }
} catch (\Exception $e) {
    // area code not set yet → allow
    return;
}
    /** 🔍 VERIFY / NOT VERIFY CHECK (ONLY ADDED PART) */
    $vendorId = (int)$vendor->getId();
    if ($vendorId) {
        $verification = $this->vendorVerificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setOrder('created_at', 'DESC')
            ->getFirstItem();
        

        if ($verification && $verification->getId() && (int)$verification->getIsVerified() === 1) {
            // 🔴 VERIFIED → message + stop
            $this->messageManager->addErrorMessage(
                __('You cannot update vendor information after verification. Please contact admin if changes are required.')
            );

            $vendor->setData($vendor->getOrigData());
            return;
        }
    }

    // 🟢 Sirf Vendor Information save par hi aayega
    $vendorData = $vendor->getData();
//    $keys = [
//     'entity_id',
//     'certificate',
//     'additional_documents',
//     'b_name',
//     'b_email'
// ];

// foreach ($keys as $key) {
//     echo $key . ' => ';
//     var_dump($vendor->getData($key));
//     echo '<br>';
// }
// die();

     if ($hasFileUpload) {
    $uploader = $this->uploaderFactory->create([
        'fileId' => 'vendor_data[certificate]'
    ]);

    $uploader->setAllowedExtensions(['pdf', 'jpg', 'png']);
    $uploader->setAllowRenameFiles(true);
    $uploader->setFilesDispersion(true);

    $mediaDir = $this->filesystem->getDirectoryWrite(
        DirectoryList::MEDIA
    );

    $path = $mediaDir->getAbsolutePath(
        'ves_vendors/attribute/certificate'
    );

    $result = $uploader->save($path);

    // 🔥 EXACT SAME PATH as vendor info
    $vendorData['certificate'] =
        'ves_vendors/attribute/certificate' . $result['file'];

    // also set on model
    $vendor->setData('certificate', $vendorData['certificate']);
}


    $this->syncService->syncAll($vendorData);
}

}
