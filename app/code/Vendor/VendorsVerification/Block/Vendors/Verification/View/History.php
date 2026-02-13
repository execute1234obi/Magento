<?php

namespace Vendor\VendorsVerification\Block\Vendors\Verification\View;

use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\Vendors\Model\Source\RegisterType;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\VendorVerification;


class History extends \Magento\Framework\View\Element\Template
{   
    

    
   protected $_vendorSession;
   protected $_storeManager;
   protected $countries;
   protected $statusOptions;
   protected $priceHelper;
   protected $_vendorVerificationFactory;
   protected $currencyFactory;
   protected $date;
   protected $timezone;
        
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,        
        VendorSession $vendorSession,        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        //\Vendor\BookAdvertisement\Model\Source\Countries $countries,        
        \Vendor\VendorsVerification\Model\Source\Status $statusOptions,
        priceHelper $priceHelper,        
        VendorVerificationFactory $vendorsVerificationFactory,        
        VerificationInfoFactory $verificationInfoFactory,        
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,        
        array $data = []
    ) {        
        
        $this->_vendorSession = $vendorSession;        
        $this->_storeManager  = $storeManager;       
        $this->countries =  $countries;        
        $this->statusOptions =  $statusOptions;        
        $this->priceHelper = $priceHelper;
        $this->_vendorVerificationFactory = $vendorsVerificationFactory;         
        $this->currencyFactory = $currencyFactory;
        $this->date = $date;                
        $this->timezone = $timezone;
        
        parent::__construct($context, $data);
    }
    
    
    public function getVendor(){
		return $this->_vendorSession->getVendor();
	}  
	
	public function getVendorVerificationHistory(){
		$vendorId = $this->getVendor()->getId();
		$isExist =  false;
		$today = $this->date->date('Y-m-d');
		$vendorVerificationcollection = $this->_vendorVerificationFactory->create()
		->getCollection()
		->addFieldToFilter('vendor_id',array('eq' => $vendorId))
		->addFieldToFilter('is_verified',array('eq' => VendorVerification::STATUS_EXPIRED))
		->addFieldToFilter('is_active',array('eq' => 0))
		->addFieldToFilter('to_date',array('lt' => $today));
		return $vendorVerificationcollection;
	}
    
    
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }   
    
    public function  getCountries (){
		$arrCountries = array();
		$countries =  $this->countries->getAllowedCountries();
		$arrCountries[]  = array('value'=>'all', 'label'=> (string) __('All Country'),'is_active'=>"1");
		foreach($countries as $key=>$value){
		    $arrCountries[] = array('value'=>$key, 'label'=> (string) $value,'is_active'=>"1");
		}
		return $arrCountries;
	} 
	
	
    
    
	
	public function  getCountriByCode($countryCode){
		$arrCountries = array();
		$countries =  $this->countries->getAllowedCountries();
		$countryName = '';
		$arrCountries['all']  = (string) __('All Country');
		foreach($countries as $key=>$value){
		    $arrCountries[$key] = $value;
		}		
		if(array_key_exists($countryCode,$arrCountries)){
			$countryName = $arrCountries[$countryCode];
		}
		return $countryName;
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
			    $label = '<label class="label-default" style="padding:5px;">'.(string) $arrStatus[$status].'</label>';
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
	
	
	
	public function getvendorVerificationStatusLabel($status){
		
		$label = '';		
		
		switch($status){			
			case 0;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) __('Not Verified').'</label>';
			    break;
			    
			 case 1;			    
			    $label = '<label class="label-success" style="padding:5px;">'.(string) __('Verified').'</label>';
			    break;
			    
			case 9;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) __('Experied').'</label>';
			    break;    
			    
			 default:
			    $label = '<label>undefine</label>';
			    break;              
		}
		return $label;
	}
	
	public function getPaymentStatusLabel($status){
		
		$label = '';		
		
		switch($status){			
			case 0;
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) __('Not Paid').'</label>';
			    break;
			    
			 case 1;
			    $label = '<label class="label-success" style="padding:5px;">'.(string) __('Paid').'</label>';
			    break;
			    
			 default:
			    $label = '<label>undefine</label>';
			    break;              
		}
		return $label;
	}
	
	public function getFormatedPrice($price){
		return $this->priceHelper->currency($price);
	}  
	
	public function getFormatedDate($date){		
		return  $this->timezone->date(new \DateTime($date))->format('F j, Y'); 		
	}
}

