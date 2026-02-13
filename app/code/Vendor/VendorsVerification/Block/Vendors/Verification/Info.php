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
        $this->_objectId = 'verification_id';
        $this->_mode = 'view';

        parent::_construct();
        
        $vendorId = $this->_vendorSession->getVendor()->getId();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        
        $vendorVerification = $this->getVendorVerification();
        
        // 1. Check karein kya status 'Entire Rejected' (5) hai
        $isEntireRejected = false;
        if ($vendorVerification && $vendorVerification->getId()) {
            if ($vendorVerification->getStatus() == 5) {
                $isEntireRejected = true;
            }
        }

        // 2. if status is Entire Rejected , it return 
        if ($isEntireRejected) {
            return; 
        }

        // 3. Normal logic (when status  is not eual to 5 )
        if (!$this->helper->isAlreadyApplied($vendorId)) {
            // Sirf tabhi "Request Verification" dikhao jab status 5 NAHI hai
            // $this->buttonList->add(
            //     'request_new',
            //     [
            //         'label' => __('Request Verification'),
            //         'class' => 'fa fa-check-circle-o primary',
            //         'onclick' => 'setLocation(\'' . $this->getVerificationUrl() . '\')'
            //     ]
            // );
        } else if (isset($vendorVerification)) {
            if (!$vendorVerification->getOrderId()) {
                $feesAmount = $this->getFormatedPrice($vendorVerification->getAmount());
                $this->buttonList->add(
                    'pay_fees',
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
