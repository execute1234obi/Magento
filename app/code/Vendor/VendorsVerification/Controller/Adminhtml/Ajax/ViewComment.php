<?php
namespace Vendor\VendorsVerification\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Vendor\VendorsVerification\Model\VerificationCommentFactory;

class ViewComment extends Action  {

    protected $verificationCommentFactory;
    
    protected $verificationDataGrop;
    
    private $resultJsonFactory;
    
    
    public function __construct(
     Context $context,
     JsonFactory $resultJsonFactory,
     VerificationCommentFactory $verificationCommentFactory,
     \Vendor\VendorsVerification\Model\Source\InfoGroup  $infodataGrop
    ) {
        $this->verificationDataGrop = $infodataGrop;        
        $this->verificationCommentFactory = $verificationCommentFactory;        
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
       }
       
       

   public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_VendorsVerification::vendor_verification_manage');
    }

   /**
  * @return \Magento\Framework\Controller\Result\Json
  */
    public function execute() {
		$result = $this->resultJsonFactory->create();
		$params = $this->getRequest()->getParams();                                       
        $commentarr = array();
		$success =  true;
		$message = '';
		try {
			
            $params = $this->getRequest()->getParams();                        
            $verificationId = $params['id']; 
            $verificationDataId = $params['dataid'];        
            $dataGroupType =  $params['grouptype'];
            
            $commentcollection = $this->verificationCommentFactory->create()
            ->getCollection()
            ->addFieldToFilter('verification_id',array('eq' => $verificationId))
            ->addFieldToFilter('detail_id',array('eq' => $verificationDataId))
            ->addFieldToFilter('datagroup_id',array('eq' => $dataGroupType));		
            if($commentcollection->getSize()){
				$message = __(' %1 Comments Found ',$commentcollection->getSize());
				// foreach($commentcollection as $comment){
				// 	$commentarr = array('date'=> $comment->getCreatedAt(),'comment'=>$comment->getComment()) ;
				// }
                 foreach ($commentcollection as $comment) {
    $commentarr[] = [
        'date'    => $comment->getCreatedAt(),
        'comment' => $comment->getComment()
    ];
}
			} else {
				$message = __(' No Comments Found ');
			}
       } catch (Exception $e) {            
		    $success =  false;
		    $message = $e->getMessage();
            //$this->messageManager->addErrorMessage($e->getMessage());
        }  
            
     $result = $this->resultJsonFactory->create();
     return $result->setData(['success' => $success, 'message'=>$message,'data'=>$commentarr]);
   }
}
