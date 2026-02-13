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


//class Update extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
class Update extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
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
    protected $storeManager;
protected $vendorHelper;
protected $detailFactory;
    /**
     * constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
    Context $context,

    VendorVerificationFactory $vendorsVerificationFactory,
    CollectionFactory $collectionFactory,
    VerificationInfoFactory $verificationInfoFactory,
    \Vendor\VendorsVerification\Model\Source\InfoGroup $infodataGrop,
    \Vendor\VendorsVerification\Model\Source\Status $statusOptions,
    VerificationCommentFactory $verificationCommentFactory,
    TimezoneInterface $timezoneInterface,
    DateTime $date,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
    \Vnecoms\Vendors\Helper\Data $vendorHelper,
    \Magento\Backend\Model\Auth\Session $authSession,
    \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
    \Vnecoms\VendorsMessage\Helper\Data $messageHelper,
    \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
    \Vnecoms\VendorsMessage\Model\Message\DetailFactory $detailFactory
) {
    parent::__construct($context);

    $this->vendorsVerificationFactory = $vendorsVerificationFactory;
    $this->verificationInfoFactory = $verificationInfoFactory;
    $this->collection = $collectionFactory;
    $this->verificationDataGrop = $infodataGrop;
    $this->statusOptions = $statusOptions;
    $this->verificationCommentFactory = $verificationCommentFactory;
    $this->timezoneInterface = $timezoneInterface;
    $this->date = $date;
    $this->storeManager = $storeManager;
    $this->storeRepository = $storeRepository;
    $this->vendorHelper = $vendorHelper;
    $this->authSession = $authSession;
    $this->vendorFactory = $vendorFactory;
    $this->_messageHelper = $messageHelper;
    $this->messageFactory = $messageFactory;
    $this->detailFactory = $detailFactory;
}

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|Page
     */
   public function execute()
{
    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
    $resultRedirect = $this->resultRedirectFactory->create();

    try {
        $params = $this->getRequest()->getParams();

        $verificationId = (int)$params['id'];
        $detailId       = (int)$params['dtl_id'];
        $dataGroupId    = (int)$params['typ_id'];
        $actionStatus   = (int)$params['actstatus'];
        $comments       = $params['comments'] ?? '';

        /** Load main verification */
        $vendorVerification = $this->vendorsVerificationFactory
            ->create()
            ->load($verificationId);

        /** Load verification detail */
        $vendorVerificationData = $this->verificationInfoFactory
            ->create()
            ->load($detailId);

        if (!$vendorVerification->getId() || !$vendorVerificationData->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid verification data.')
            );
        }

        $adminUser   = $this->getCurrentUser();
        $adminUserId = $adminUser ? (int)$adminUser->getUserId() : 0;
        $vendorId    = (int)$vendorVerification->getVendorId();

        $dataGroupName  = $this->getDataGroupLabel($vendorVerificationData->getDatagroupId());
        $statusLabel    = $this->getStatusLabel($actionStatus);
        $now            = $this->date->gmtDate();

        switch ($actionStatus) {

            /** ================= RESUBMIT ================= */
            case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__RESUBMIT:

                $vendorVerificationData->setStatus($actionStatus);
                $vendorVerificationData->setApproval(
                    \Vendor\VendorsVerification\Model\Source\Approval::STATUS_PENDING_UPDATE
                );
                $vendorVerificationData->setUpdatedAt($now);
                $vendorVerificationData->save();

                /** Save admin comment */
                $verificationComment = $this->verificationCommentFactory->create();
                $verificationComment->setVerificationId($verificationId);
                $verificationComment->setDetailId($detailId);
                $verificationComment->setDatagroupId($dataGroupId);
                $verificationComment->setComment($comments); // ✅ FIXED
                $verificationComment->setVendorDataupdate(
                    $vendorVerificationData->getVendorData()
                );
                $verificationComment->setAdminUserid($adminUserId);
                $verificationComment->setStatus(0);
                $verificationComment->setCreatedAt($now);
                $verificationComment->save();

                /** Notify vendor */
                $this->sendMessage(
                    $vendorId,
                    __('Verification Re-submission Request'),
                    __('Admin Comment: %1', $comments)
                );

                $this->messageManager->addSuccessMessage(__('Verification marked as Re-submit.'));
                break;

            /** ================= REJECT ================= */
            case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__REJECTED:

                $vendorVerificationData->setStatus($actionStatus);
                $vendorVerificationData->setApproval(
                    \Vendor\VendorsVerification\Model\Source\Approval::STATUS_UNAPPROVED
                );
                $vendorVerificationData->setUpdatedAt($now);
                $vendorVerificationData->save();

                $verificationComment = $this->verificationCommentFactory->create();
                $verificationComment->setVerificationId($verificationId);
                $verificationComment->setDetailId($detailId);
                $verificationComment->setDatagroupId($dataGroupId);
                $verificationComment->setComment($comments);
                $verificationComment->setVendorDataupdate(
                    $vendorVerificationData->getVendorData()
                );
                $verificationComment->setAdminUserid($adminUserId);
                $verificationComment->setStatus(0);
                $verificationComment->setCreatedAt($now);
                $verificationComment->save();

                $this->messageManager->addSuccessMessage(__('Verification Rejected.'));
                break;

            /** ================= APPROVE ================= */
            case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__VERIFIED:

                $vendorVerificationData->setStatus($actionStatus);
                $vendorVerificationData->setApproval(
                    \Vendor\VendorsVerification\Model\Source\Approval::STATUS_APPROVED
                );
                $vendorVerificationData->setUpdatedAt($now);
                $vendorVerificationData->save();

                $this->messageManager->addSuccessMessage(__('Verification Approved.'));
                break;
        }

        /** Update main verification status */
        if (method_exists($this, 'updateVendorVerification')) {
            $this->updateVendorVerification($verificationId);
        }

    } catch (\Throwable $e) {
        $this->messageManager->addErrorMessage($e->getMessage());
    }

    /** Redirect back to admin view page */
    return $resultRedirect->setPath(
        'vendorverification/index/view',
        ['id' => $verificationId]
    );
}


    
    private function updateVendorVerification($vedificationId){
		    $resultRedirect = $this->resultRedirectFactory->create();        
		    $vendorVerification = $this->vendorsVerificationFactory->create()->load($vedificationId);		    
		    $vendorId = $vendorVerification->getVendorId();
            $vendorVerificationIncId = $vendorVerification->getData('inc_id');
            $vendorVerificationDataCollection = $this->collection->create()->addFieldToFilter('verification_id',array('eq'=>$vedificationId));                        
            $isallDataApproved = true;
            foreach($vendorVerificationDataCollection as $verificationData){            			
               if($verificationData->getData('approval') != \Vendor\VendorsVerification\Model\Source\Approval::STATUS_APPROVED){
				   $isallDataApproved =  false;
				   break;
			   }
		    }		    
		    if($isallDataApproved == true){// Approved vendor Verification
				if($vendorVerification->getIsPaid()==0){
				    $this->messageManager->addErrorMessage( __('Seller Verification %1 ',$vendorVerificationIncId) . __(' Payment is due. Seller Verification could not be Approved.'));            
				    return $resultRedirect->setPath('vendorverification/index/index/');
			    }
			   //Update vendor verification to APPROVED
                $duration = (int) $vendorVerification->getData('months_booked');
                $now = new \DateTime();                    
	            $time = '';            
	            //$currentTime = $this->date->date();
                //$expiryTime = strtotime($currentTime.$time);
	            $time = "+$duration months";
	            $currentTime = $this->timezoneInterface->date($now)->format('Y-m-d'); //$this->date->date();
	            $expiryTime = strtotime($currentTime.$time);    
	            $expiryTime = $this->timezoneInterface->date($expiryTime)->format('Y-m-d');               
                if(!$vendorVerification->getFromDate()){
                 $vendorVerification->setFromDate($currentTime);
		        }
		        if(!$vendorVerification->getToDate()){
                  $vendorVerification->setToDate($expiryTime);                        
		       }
               $vendorVerification->setIsVerified(1);
               $vendorVerification->setstatus(\Vendor\VendorsVerification\Model\Source\Approval::STATUS_APPROVED);            
               $vendorVerification->save();  
               
               //Update vendor Attribute for Vendor Verification
               $vendor = $this->vendorFactory->create()->load($vendorId);                     
               $vendorData['is_verified'] = 1;          
               $vendor->addData($vendorData);              
               $vendor->save();               
                       
               //Send Message to Vendor
			   $messageSubject = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			   $messageContent = __('Your  Seller %1 Verification  #',$vendorVerificationIncId).__('has been Approved');
			   $result = $this->sendMessage($vendorId,$messageSubject,$messageContent);			
               $this->messageManager->addSuccessMessage(__('Seller Verification  # %1 has been  APPROVED',$vendorVerification->getData('inc_id')));            
            
			} else { // Unapproved vendor Verification
				//Update vendor verification to UNAPPROVED               
				
               //$vendorVerification->setFromDate(null);
               //$vendorVerification->setToDate(null);
               $vendorVerification->setIsVerified(0);
               $vendorVerification->setstatus(\Vendor\VendorsVerification\Model\Source\Approval::STATUS_UNAPPROVED);            
               $vendorVerification->save();            
               $this->messageManager->addSuccessMessage(__('Seller Verification  # %1 has been  UNAPPROVED',$vendorVerification->getData('inc_id')));            
			}
		    return;
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
		
		$label = '<label class="label-default" style="font-weight:strong;pading:5">'.(string) $arrStatus[$datagroupId].'</label>';
		/*switch($datagroupId){
			case \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_INFORMATION;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) $arrStatus[$datagroupId].'</label>';
			    break;
			case \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_ADDRESS;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) $arrStatus[$datagroupId].'</label>';
			    break;
			case \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_CONTACT;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) $arrStatus[$datagroupId].'</label>';
			    break;
			    
			 case \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) $arrStatus[$datagroupId].'</label>';
			    break;
			    
			 default:
			    $label = '<label>undefine</label>';
			    break;              
		}*/
		
		return $label;
	}
	
	public function getStatusLabel($status){
		$statusOptions = $this->statusOptions->getAllOptions();
		$label = '';		
		$arrStatus= array();
		foreach($statusOptions as $key=>$option){			
		    $arrStatus[$option['value']] = (string) $option['label'];
		}
		
		
		switch($status){
			case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING;
			    $label = '<label class="label-default" >'.(string) $arrStatus[$status].'</label>';
			    break;
			case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__RESUBMIT;
			    $label = '<label class="label-primary" style="padding:5px;">'.(string) $arrStatus[$status].'</label>';
			    break;
			case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__REJECTED;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) $arrStatus[$status].'</label>';
			    break;
			    
			 case \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__VERIFIED;
			    $label = '<label class="label-success" style="padding:5px;">'.(string) $arrStatus[$status].'</label>';
			    break;
			    
			 default:
			    $label = '<label>undefine</label>';
			    break;              
		}
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

$msgDetailData = [
    'sender_id' => 0,
    'sender_email' => $sender->getEmail(),
    'sender_name' => $sender->getFirstName().' '.$sender->getLastName().' [ Admin ]',
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

