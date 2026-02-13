<?php

namespace Vendor\VendorsVerification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;

use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo\CollectionFactory;

use Vnecoms\VendorsMessage\Model\Message;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;


class Approve extends Action 
{
	
	protected $vendorsVerificationFactory;
    
    protected $collection;    
    
    
    protected $verificationDataGrop;
    
    protected $statusOptions;
    
    protected $storeRepository;
	    
    protected $timezoneInterface;
    
    protected $date;
        
    protected $helper;   
    
    //protected $coreRegistry;
    protected $_coreRegistry;

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
    protected $storeManager;
protected $detailFactory;

    /**
     * Page factory
     *
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

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
        VerificationInfoFactory $verificationInfoFactory,
        CollectionFactory $collectionFactory,
        \Vendor\VendorsVerification\Model\Source\InfoGroup  $infodataGrop,
        \Vendor\VendorsVerification\Model\Source\Status $statusOptions,        
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
        $this->collection = $collectionFactory;
        $this->verificationDataGrop = $infodataGrop;
        $this->statusOptions =  $statusOptions;        
        $this->storeRepository = $storeRepository;
        $this->storeManager    = $storeManager;       
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->authSession = $authSession;
        $this->vendorFactory = $vendorFactory;
        $this->_messageHelper = $messageHelper;
        $this->messageFactory = $messageFactory;
        $this->detailFactory = $detailFactory;
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
            $verificationId =  $params['id'];            
            $vendorVerification = $this->vendorsVerificationFactory->create()->load($verificationId);
            $vendorVerificationIncId = $vendorVerification->getData('inc_id');
            $vendorVerificationDataCollection = $this->collection->create()->addFieldToFilter('verification_id',array('eq'=>$verificationId));                        
            $isallDataApproved = true;            
            if($vendorVerification->getIsPaid()==0){
				$this->messageManager->addErrorMessage( __('Seller Verification %1 ',$vendorVerificationIncId) .  __(' Payment is Due. Seller Verification Could Not be Approved'));            
				return $resultRedirect->setPath('vendorverification/index/index/');
			}
            foreach($vendorVerificationDataCollection as $verificationData){            
               if($verificationData->getData('approval') != \Vendor\VendorsVerification\Model\Source\Approval::STATUS_APPROVED){
				   $isallDataApproved =  false;
				   $Status = $verificationData->getData('status');
				   $dataGroupName =  $this->getDataGroupLabel($verificationData->getData('datagroup_id'));        
				   $statusLabel = $this->getStatusLabel($Status);				   				   
				   $this->messageManager->addErrorMessage( __('Seller Verification %1 ',$vendorVerificationIncId) .$dataGroupName. __('status is ').$statusLabel );            
			   }
		    }
		    if(!$isallDataApproved ){
				$this->messageManager->addErrorMessage(__('This Seller Verification %1 could not be Approve . ',$vendorVerificationIncId));            
				return $resultRedirect->setPath('vendorverification/index/index/');
			}
            
            $adminuser = $this->getCurrentUser();
            $adminuserid = $adminuser->getUserId();            
            $vendorId = $vendorVerification->getData('vendor_id');
            
            //Update vendor verification
            $duration = (int) $vendorVerification->getData('months_booked');
            $time = '';            
            $time = "+$duration months";
            $currentTime = $this->date->date();
            $expiryTime = strtotime($currentTime.$time);            
            
            $vendorVerification->setFromDate($currentTime);
            $vendorVerification->setToDate($expiryTime);                        
            $vendorVerification->setIsVerified(1);
            $vendorVerification->setstatus(\Vendor\VendorsVerification\Model\Source\Approval::STATUS_APPROVED);            
            $vendorVerification->save();            
            $this->messageManager->addSuccessMessage(__('The Seller Verification %1 has been successfully verified . ',$vendorVerificationIncId));
            
            //Update vendor Attribute for Vendor Verification
             $vendor = $this->vendorFactory->create()->load($vendorId);                     
             $vendorData['is_verified'] = 1;          
             $vendor->addData($vendorData);              
             $vendor->save();  
            
            
            //Send Message to Vendor
			$messageSubject = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			$messageContent = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			$result = $this->sendMessage($vendorId,$messageSubject,$messageContent);            
		    
		 } catch (Exception $e) {            
            $this->messageManager->addErrorMessage($e->getMessage());            
        }  	
		 /*$resultRedirect->setPath(
                'vendorverification/index/view/',
                ['id' => $this->getRequest()->getParam('id')]
            );*/
        return $resultRedirect->setPath('vendorverification/index/index/');    
      //  return $resultRedirect;        
        
    }
    
    
    
     public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_VendorsVerification::vendor_verification_manage');
    }
    
    private function getCurrentUser()
    {
       return $this->authSession->getUser();
    }
    
    private function getDataGroupLabel($datagroupId){
		$statusOptions = $this->verificationDataGrop->getAllOptions();
		$label = '';		
		$arrStatus= array();
		foreach($statusOptions as $key=>$option){			
		    $arrStatus[$option['value']] = (string) $option['label'];
		}
		
		//$label = (string) $arrStatus[$datagroupId];		
        $label = $arrStatus[$datagroupId] ?? '';
		return $label;
	}
	
	public function getStatusLabel($status){
		$statusOptions = $this->statusOptions->getAllOptions();
		$label = '';		
		$arrStatus= array();
		foreach($statusOptions as $key=>$option){			
		    $arrStatus[$option['value']] = (string) $option['label'];
		}
		
	    //$label = (string) $arrStatus[$status];
        $label = $arrStatus[$status] ?? '';

		return $label;
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
                'owner_id' => 'Vendor\Utility\Helper\Data'::CUSTOMER_ID_FOR_ADMIN_MESSAGE_SENDER, // $sender->getUserId(),
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
                'sender_id' => 'Vendor\Utility\Helper\Data'::CUSTOMER_ID_FOR_ADMIN_MESSAGE_SENDER, // $sender->getUserId(),
                'sender_email' => $sender->getEmail(),
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

