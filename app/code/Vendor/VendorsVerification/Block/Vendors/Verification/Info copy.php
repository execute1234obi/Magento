<?php

namespace Vendor\VendorsVerification\Block\Vendors\Verification;

use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Magento\Framework\Pricing\Helper\Data as priceHelper;

class Info extends \Vnecoms\Vendors\Block\Vendors\Widget\Container
{      
    
     /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;  
    
    
    protected $_vendorVerificationFactory;
    
    protected $helper;  
    
    protected $priceHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        VendorSession $vendorSession,
        \Vendor\VendorsVerification\Helper\Data  $helper,   
        VendorVerificationFactory $vendorsVerificationFactory,     
        priceHelper $priceHelper,   
        array $data = []
    ) {
        $this->_coreRegistry = $registry;        
        $this->_vendorSession = $vendorSession;
        $this->_vendorVerificationFactory = $vendorsVerificationFactory;         
        $this->helper = $helper;  
        $this->priceHelper = $priceHelper;      
        
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        //echo "hi"; die();
        $this->_objectId = 'verification_id';
        $this->_mode = 'view';

        parent::_construct();
        
        $vendorId =  $this->_vendorSession->getVendor()->getId();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $vendorVerification =  $this->getVendorVerification();
        if(! $this->helper->isAlreadyApplied($vendorId)){
            //echo "Hello";
           /*$this->buttonList->add(
                           'print',
                [
                    'label' => __('Request Verification'),
                    'class' => 'fa fa-check-circle-o',
                    'onclick' => 'setLocation(\'' . $this->getVerificationUrl() . '\')'
                ]
            );*/
         } else if(isset($vendorVerification)){
			 //if($vendorVerification->getIsPaid()==0){			 
			 if(!$vendorVerification->getOrderId()){
               //echo "hi";die();
				 $feesAmount =  $this->getFormatedPrice($vendorVerification->getAmount());
				 $this->buttonList->add(
                'print',
                [
                    'label' => __('Pay %1 Verification Fees Now', $feesAmount),
                    'class' => 'fa fa-credit-card label-danger',
                    'onclick' => 'setLocation(\'' . $this->getVerificationFeesUrl($vendorVerification->getId()) . '\')'
                ]
            );
			 }
		 }

    }
    
    private function getVendorVerification(){
		$vendorId =  $this->_vendorSession->getVendor()->getId();
		$isExist =  false;
		$vendorVerificationcollection = $this->_vendorVerificationFactory->create()
		->getCollection()
		->addFieldToFilter('vendor_id',array('eq' => $vendorId))
		->addFieldToFilter('is_active',array('eq' => 1))
		//->addFieldToFilter('is_verified',array('eq' => 0))
		->setOrder('verification_id','DESC');		
		$count = $vendorVerificationcollection->getSize();
		$isExist = ($count>0)?true:false;
		$vendorVerification = $vendorVerificationcollection->getFirstItem();
		return $vendorVerification;
	}    
   
    
    /**
     * @return string
     */
    public function getVerificationUrl()
    {
		
        return $this->getUrl('vendorverification/verification/new');
    }
    
    
    /**
     * @return string
     */
    // public function getVerificationFeesUrl($id)
    // {
	// 	$queryParams = ['id' => $id];		
    //     return $this->getUrl('../../vendorverification/quotes/addtocart',['_current' => true,'_use_rewrite' => true, '_query' => $queryParams]);
    // }
    public function getVerificationFeesUrl($id)
{
    return $this->getUrl(
        'vendorverification/quotes/addtocart',
        ['_query' => ['id' => $id]]
    );
}

    public function getFormatedPrice($price){
		return $this->priceHelper->currency($price);
	}    


}
