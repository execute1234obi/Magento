<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\JsonFactory;
use Vendor\VendorsVerification\Model\VerificationCommentFactory;


class ViewComment extends  \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';    
    
    protected $verificationCommentFactory;
    
    protected $verificationDataGrop;
    
    private $resultJsonFactory;
    
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        VerificationCommentFactory $verificationCommentFactory,
        \Vendor\VendorsVerification\Model\Source\InfoGroup  $infodataGrop
    ) {
        $this->verificationDataGrop = $infodataGrop;        
        $this->verificationCommentFactory = $verificationCommentFactory;        
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context); 
    }


    /**
     * @return void
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
