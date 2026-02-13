<?php
namespace Vendor\VendorsVerification\Controller\Vendors\Uploader;

//use Magento\Framework\App\Action\Context;
use Vnecoms\Vendors\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;

//class Ajax extends \Magento\Framework\App\Action\Action
class Ajax extends \Vnecoms\Vendors\Controller\Vendors\Action implements CsrfAwareActionInterface
{
    protected $resultPageFactory;
    protected $jsonHelper;
    protected $filesystem;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected WriteInterface $mediaDirectory;

    private $logger;
    protected $storeManager;

 public function __construct(
    Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    ManagerInterface $messageManager,
    Filesystem $filesystem,
    UploaderFactory $fileUploader,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Psr\Log\LoggerInterface $logger
) {
    $this->resultPageFactory = $resultPageFactory;
    $this->jsonHelper       = $jsonHelper;
    $this->messageManager  = $messageManager;
    $this->filesystem      = $filesystem;
    $this->fileUploader    = $fileUploader;
    $this->logger          = $logger;
    $this->storeManager    = $storeManager;

    // ✅ SAFE for PHP 8.2
    $this->mediaDirectory = $filesystem->getDirectoryWrite(
        DirectoryList::MEDIA
    );

    parent::__construct($context);
}

/**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(
        RequestInterface $request
    ): ?bool {
        return true;
    }
    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //echo("Hi");exit();
    $error = false;
    // Do something with the file post var_dump($_FILES['files'])
    //$uploadedFile = $this->uploadFile($_FILES['files']['name']);     
    $resultdata = $this->uploadFile();
    //print_r($resultdata);
    //exit();
    //$error  = (is_array($resultdata) && isset($resultdata['file'])) ? true:false;    
    //$result = ['error'=>$error,'success'=> ($error) ? false : true,'data'=>$resultdata ];
	try {
            return $this->jsonResponse($resultdata);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
    
    public function uploadFile()
    {
		
        // this folder will be created inside "pub/media" folder
        $yourFolderName = 'vendor-verification-documents/';
 
        // "my_custom_file" is the HTML input file name
        $yourInputFileName = $this->getRequest()->getParam('fldname');
        //$yourInputFileName = 'vendordocs';
        //$yourInputFileName  = 'vendorverification-docs';

        try{
            $file = $this->getRequest()->getFiles($yourInputFileName);
            //$this->logger->info("Ajax File Upload",$file);
            
            //$this->logger->info("i am here 1");
            $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;
            //$this->logger->info("i am here 2");
 
            if ($file && $fileName) {
			//	$this->logger->info("i am here 3");

                $target = $this->mediaDirectory->getAbsolutePath($yourFolderName);        
                list($fname,$fileExtension) =  explode(".",$fileName);
                $this->logger->info("fileExtension=".$fileExtension);
                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                $uploader = $this->fileUploader->create(['fileId' => $yourInputFileName]);
                
                // set allowed file extensions
                $uploader->setAllowedExtensions(['jpg','jpeg', 'pdf', 'doc','docx', 'png']);
                
                // allow folder creation
                $uploader->setAllowCreateFolders(true);
 
                // rename file name if already exists 
                $uploader->setAllowRenameFiles(true);
                
                // rename the file name into lowercase
                // but this one is not working
                // we can simply use strtolower() function to rename filename to lowercase
                // $uploader->setFilenamesCaseSensitivity(true);
                
                // enabling file dispersion will 
                // rename the file name into lowercase
                // and create nested folders inside the upload directory based on the file name
                // for example, if uploaded file name is IMG_123.jpg then file will be uploaded in
                // pub/media/your-upload-directory/i/m/img_123.jpg
                // $uploader->setFilesDispersion(true);         
 
                // upload file in the specified folder
                $result = $uploader->save($target);
              //  $this->logger->info("Upload Result",$result);
                
 
                //echo '<pre>'; print_r($result); exit;
 
                if ($result['file']) {
                    $this->messageManager->addSuccess(__('File has been successfully uploaded.')); 
                }
                
                //return $target . $uploader->getUploadedFileName();
                $result['url'] = $this->getMediaUrl().$yourFolderName.$uploader->getUploadedFileName();
                if(strtolower($fileExtension)=='pdf'){
					$result['url'] = $this->getMediaUrl().'porto/'.'vendor-pdf.png';
					$result['previewType'] ='image';
				}if(strtolower($fileExtension)=='doc' || strtolower($fileExtension)=='docx'){					
					$result['url'] = $this->getMediaUrl().'porto/'.'vendor-doc.png';
					$result['previewType'] ='image';
				}
                //$this->logger->info("uploadFile Result",$result);
                return $result;
                
            } else {				
				//$this->logger->info("Ajax File Upload file=",$file);
				//$this->logger->info("Ajax File Upload fileName=".$fileName);
			}
        } catch (\Exception $e) {
			$this->logger->info("Upload Err   ".$e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
 
        return false;
    }
    
    public function getMediaUrl()
	{		
		$currentStore = $this->storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
    public function createException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

   
}
