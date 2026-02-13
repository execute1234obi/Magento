<?php

namespace Vendor\VendorsVerification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo\CollectionFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\VerificationCommentFactory;
use Vnecoms\VendorsMessage\Model\Message;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Vnecoms\VendorsMessage\Helper\Data as VendorsMessageHelper;
use Vendor\VendorsVerification\Model\Source\Status;

use Magento\Framework\Exception\LocalizedException;


class Delete extends Action 
{
	
	protected $vendorsVerificationFactory;
	
	protected $collection;    
    
    protected $verificationInfoFactory;
    
    protected $verificationCommentFactory;
    
    protected $verificationDataGrop;
    
    protected $statusOptions;
    
    protected $storeRepository;
	    
    protected $timezoneInterface;
    
    protected $date;
        
    protected $helper;   
    
    protected $coreRegistry;
    
    protected $_messageHelper;
    
    protected $messageFactory;
    
    protected $vendorFactory;
    
    protected $authSession;
    
    /**
     * Page result factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Page factory
     *
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    
    protected $_coreRegistry; 

     protected $_storeManager;
     protected $storeManager;
     protected $detailFactory;

    /**
     * constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,                
        VendorVerificationFactory $vendorsVerificationFactory,    
        CollectionFactory $collectionFactory,    
        VerificationInfoFactory $verificationInfoFactory,
        \Vendor\VendorsVerification\Model\Source\InfoGroup  $infodataGrop,
        \Vendor\VendorsVerification\Model\Source\Status $statusOptions,
        VerificationCommentFactory $verificationCommentFactory,
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,        
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        \Vnecoms\VendorsMessage\Model\Message\DetailFactory $detailFactory         
    ) {
        $this->resultPageFactory = $resultPageFactory;        
        $this->_coreRegistry = $coreRegistry;
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
        $this->verificationInfoFactory = $verificationInfoFactory;        
        $this->collection = $collectionFactory;
        $this->verificationDataGrop = $infodataGrop;
        $this->statusOptions =  $statusOptions;
        $this->verificationCommentFactory = $verificationCommentFactory;
        $this->storeRepository = $storeRepository;
        $this->storeManager    = $storeManager;       
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->authSession = $authSession;
        $this->vendorFactory = $vendorFactory;
        $this->_messageHelper = $messageHelper;
        $this->messageFactory = $messageFactory;
        $this->detailFactory = $detailFactory;
        $this->_storeManager = $storeManager;
        

        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|Page
     */
    public function execute()
    {

		$resultRedirect = $this->resultRedirectFactory->create();        
		try {
            $params = $this->getRequest()->getParams();            
            $vedificationId =  $params['id'];
            $comments = (isset($params['comments'])) ? strip_tags($params['comments']) : '';
            $vendorVerification = $this->vendorsVerificationFactory->create()->load($vedificationId);
            //if(! $vendorVerification->getVerificationId()){
            if (!$vendorVerification->getId()) {

				$this->messageManager->addErrorMessage(__(' Seller Verification not found.'));
				$resultRedirect->setPath('vendorverification/index/index/');
                return $resultRedirect;   
			}
			//echo "<pre>".print_r($vendorVerification->getData(), 1)."</pre>";
			//die;
            $canCancel = true;
            $adminuser = $this->getCurrentUser();
            $adminuserid = $adminuser->getUserId();            
            $vendorId = $vendorVerification->getData('vendor_id');
		    
		    if($vendorVerification->getData('is_verified') && $vendorVerification->getData('is_paid')){				
				$this->messageManager->addErrorMessage(__('Unabel to cancel entire seller verification # %1 as Verification is Paid and verified',$vendorVerification->getData('inc_id')));            
				/*throw new LocalizedException(
                __('A customer with the same email already exists in an associated website.')
               );*/
               $canCancel =  false;
			}
			
			/*$currentTime = $this->date->date();            
            if($currentTime >= $vendorVerification->getData('from_date') ){
			}*/
			
			if($canCancel){
		        $this->deleteVendorVefirication($vedificationId);// Delete verifiation 
                return $resultRedirect->setPath('vendorverification/index/index/');
		    }
		    
		    
		 } catch (Exception $e) {            
            $this->messageManager->addErrorMessage($e->getMessage());            
         }  	
		 $resultRedirect->setPath(
                'vendorverification/index/view/',
                ['id' => $this->getRequest()->getParam('id')]
            );
        return $resultRedirect;        
        
    }
    
    private function deleteVendorVefirication($vedificationId){
		    
		    try {
		       $vendorVerification = $this->vendorsVerificationFactory->create()->load($vedificationId);		    
		       $vendorId = $vendorVerification->getVendorId();
		       $vendorVerificationIncId = $vendorVerification->getData('inc_id'); 
		       $vendorVerification->delete();
		       $storeId = $this->_storeManager->getDefaultStoreView()->getStoreId();		       
               $verificationUrl = $this->storeManager->getStore($storeId)->getUrl('vendors/vendorverification/verification/new');
               
               //Update vendor Attribute for Vendor Verification
               $vendor = $this->vendorFactory->create()->load($vendorId);                     
               $vendorData['is_verified'] = 0;          
               $vendor->addData($vendorData);              
               $vendor->save();  
               
               
		      //Send Message to Vendor
			   $messageSubject = __('Your entire Seller Verification # %1  has been Deleted',$vendorVerificationIncId);			   			   
			   $messageContent = __('Your entire Seller Verification # %1 has been Deleted. Please  go through the whole verification process again.',$vendorVerificationIncId);
			   $messageContent .= __('Please click <a target="_blank" href="'.$verificationUrl.'"> here </a> to submit Verification request ');
			   $result = $this->sendMessage($vendorId,$messageSubject,$messageContent);						   
               $this->messageManager->addSuccessMessage(__('Entire Seller Verification # %1 has been Deleted.',$vendorVerificationIncId));            
               
		    } catch (Exception $e) {            
               $this->messageManager->addErrorMessage($e->getMessage());            
            }  	
	        return $this;
		    
		    /*$vendorId = $vendorVerification->getVendorId();
            $vendorVerificationIncId = $vendorVerification->getData('inc_id');            
            $isallDataApproved = true;            
		    if($isallDataApproved == true){// Approved vendor Verification
				if($vendorVerification->getIsPaid()==0){
				    $this->messageManager->addErrorMessage( __('Seller Verification %1 ',$vendorVerificationIncId) . __(' Payment is due. Seller Verification could not be Approved.'));            
				    return $resultRedirect->setPath('vendorverification/index/index/');
			    }			     
               //Send Message to Vendor
			   $messageSubject = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			   $messageContent = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			   $result = $this->sendMessage($vendorId,$messageSubject,$messageContent);			
               $this->messageManager->addSuccessMessage(__('Seller Verification  # %1 has been  APPROVED',$vendorVerification->getData('inc_id')));            
            
			} 
            $this->messageManager->addSuccessMessage(__('   Seller Verification  # %1 has been  UNAPPROVED',$vendorVerification->getData('inc_id')));            			
            */
		    
	}
    
    
    
     public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_VendorsVerification::vendor_verification_manage');
    }
    
    private function getCurrentUser()
    {
       return $this->authSession->getUser();
    }
   
	
	
	
	private function sendMessage($vendorId,$messageSubject,$messageContent)
    {
        
        $request = $this->getRequest();
        $vendor = $this->vendorFactory->create()->load($vendorId);        
        if($vendor->getId() ){        
            $receiver = $vendor->getCustomer();           
            $sender =  $this->getCurrentUser();            
            $message = $this->messageFactory->create();
            $messageDetail = $this->detailFactory->create();
            $identifier = md5(md5($sender->getUserId().$receiver->getUserId()).time());
            $senderMsgData = [
                'identifier' => $identifier,
                'owner_id' => 0,
                'status' => Message::STATUS_SENT,
                'is_inbox' => 0,
                'is_outbox' => 1,
                'is_deleted' => 0,
            ];

            $receiverMsgData = [
                'identifier' => $identifier,
                'owner_id' => $receiver->getId(),
                'status' => Message::STATUS_UNDREAD,
                'is_inbox' => 1,
                'is_outbox' => 0,
                'is_deleted' => 0,
            ];

            /*$attachments = $request->getParam('attachments');
            if($attachments){
                $attachments = explode("||", $attachments);
            }*/

            $msgDetailData =[
               'sender_id' => 0,
                'sender_name' => $sender->getFirstName().' '.$sender->getLastName().' [ '.__('Admin').' ]',
                'receiver_id' => $receiver->getId(),
                'receiver_email' => $receiver->getEmail(),
                'receiver_name' => $receiver->getName(),
                'subject' => $messageSubject,
                'content' => $messageContent,
                'is_read' => 0,
                'attachments' => null
            ];
            

            $errors = [];
            $warnings = [];

            $transport = new \Magento\Framework\DataObject(
                [
                    'detail_data'=>$msgDetailData ,
                    'errors'=>$errors,
                    'warnings' => $warnings
                ]
            );
            //Save the message to sender outbox
            $this->_eventManager->dispatch(
                'messsage_prepare_save',
                [
                    'transport'=>$transport ,
                ]
            );

            $errors = $transport->getErrors();
            $warnings = $transport->getWarnings();

            try {

                if($errors){
                    throw new \Exception(implode("<br />", $errors));
                }

                $result = [];

                $message->setData($senderMsgData)->save();
                $messageDetail->setData($msgDetailData)->setMessageId($message->getId())->save();

                if($warnings){
                    $result["msg"] = implode("<br />", $warnings);
                    $warningData = [
                        'message_id'  => $message->getId(),
                        'detail_message_id' =>   $messageDetail->getId()
                    ];
                    $warning = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Warning');
                    $warning->setData($warningData)->save();
                }

                //Save the message to receiver inbox
                $message->setData($receiverMsgData)->save();
                $messageDetail->setData($msgDetailData)->setMessageId($message->getId())->save();

                //Send notification email to receiver
                $this->_messageHelper->sendNewReviewNotificationToCustomer($messageDetail);
                $result["error"] = false;

            }catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result = [
                    'error' => true,
                    'msg' => $e->getMessage()
                ];
            } catch (\RuntimeException $e) {
                $result = [
                    'error' => true,
                    'msg' => $e->getMessage()
                ];
            } catch (\Exception $e) {
                $result = [
                    'error' => true,
                    'msg' => $e->getMessage()
                ];
            }
            
        }else {
			   $result = [
                    'error' => true,
                    'msg' => 'No vendor found'
                ];
		}
		
		return $result;
    }

}

