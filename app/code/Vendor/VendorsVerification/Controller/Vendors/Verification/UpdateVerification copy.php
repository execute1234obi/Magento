<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use \Vnecoms\Vendors\App\Action\Context;
use \Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class UpdateVerification  extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';
    
    protected $vendorsVerificationFactory;
    
    protected $verificationDataGrop;
    
    protected $verificationInfoFactory;
    
    protected $storeRepository;
	    
    protected $timezoneInterface;
    
    protected $date;
        
    protected $_customerRepositoryInterface;
    
    protected $helper;
    
    protected $_messageManager;
        
	/**
	* @var \Magento\Store\Model\StoreManagerInterface
	*/
	protected $storeManager;
    
     public function __construct(
        Context $context,
        VendorSession $vendorSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        VendorVerificationFactory $vendorsVerificationFactory,        
        VerificationInfoFactory $verificationInfoFactory,
        \Vendor\VendorsVerification\Model\Source\InfoGroup  $infodataGrop,
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,        
        \Vendor\VendorsVerification\Helper\Data  $helper,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->_vendorSession = $vendorSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_coreRegistry = $coreRegistry;
        $this->_vendorHelper = $vendorHelper;
        $this->verificationDataGrop = $infodataGrop;
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->_customerRepositoryInterface = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->storeManager    = $storeManager;       
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->helper = $helper;
        $this->_messageManager = $context->getMessageManager();
        parent::__construct($context);
    }
    
    /**
     * @return void
     */
    public function execute()
    { 
        
        try {
            $vendor =  $this->_vendorSession->getVendor();
            $vendorCustomer =  $vendor->getCustomer();
            $vendorId =  $vendor->getId();
            $data =  $this->getRequest()->getParams();
            $verificationId = $data['id'];
            $typ_id = $data['typ_id'];
            $detail_id = $data['dtl_id'];            
            $verification =  $vendorVerification = $this->vendorsVerificationFactory->create()->load($verificationId);              
            $verificationInfo = $this->verificationInfoFactory->create()->load($detail_id);            
            $dataGroupName = strtoupper($this->getDataGroupLabel($verificationInfo->getData('datagroup_id')));        
            
            /*echo "<pre>".print_r($data,1)."</pre>";
            echo "<hr />";
            echo "<pre>".print_r($verification->getData(),1)."</pre>";
            echo "<hr />";
            echo "<pre>".print_r($verificationInfo->getData(),1)."</pre>";
            echo "<hr />";
            echo "Status=".$verificationInfo->getStatus();
            die;*/
            
            if($verification->getData('vendor_id') != $vendorId){            
				$this->_messageManager->addError(__("invalid Access for verification"));
                return $this->_redirect('vendorverification/verification/index/');				
			} else if ($verification->getData('is_verified') ==2){
				$this->_messageManager->addError(__(" Verification process already completed."));
                return $this->_redirect('vendorverification/verification/index/');		
		    }else if($verificationInfo->getStatus() != \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__RESUBMIT){
				$this->_messageManager->addError(__(" Seller Verification %1 data is not allowed to update.", $dataGroupName));
                return $this->_redirect('vendorverification/verification/index/');		
			}
				
                        
            //echo "<pre>".print_r($data,1)."</pre>";            
                        
            // vendor Verification Data : Vendor Info
            
            if($typ_id == \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_INFORMATION){
			    $dataGroupId =  \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_INFORMATION;	
                $arrDataVendorInformation = array();
                /*$arrDataVendorInformation['Vendor-country'] = $data['Vendor-country'];
                $arrDataVendorInformation['Vendor-name'] = $data['Vendor-name'];
                $arrDataVendorInformation['Vendor-type'] = $data['Vendor-type'];
                $arrDataVendorInformation['Vendor-description'] = $data['Vendor-description'];
                $arrDataVendorInformation['Vendor-website'] = $data['Vendor-website'];
                $arrDataVendorInformation['Vendor-phone'] = $data['Vendor-phone'];
                $arrDataVendorInformation['Vendor-email'] = $data['Vendor-email'];*/
                $arrDataVendorInformation['Vendor-country'] = (isset($Vendorcountry))? $Vendorcountry:'';            
                $arrDataVendorInformation['Vendor-name'] = (isset($data['Vendor-name']))? $data['Vendor-name']:'';            
                $arrDataVendorInformation['Vendor-type'] = (isset($Vendortype))? $Vendortype:'';            
                $arrDataVendorInformation['Vendor_category'] = (isset($Vendorcategory))? $Vendorcategory:'';                        
                $arrDataVendorInformation['Vendor-description'] = (isset($data['Vendor-description']))? $data['Vendor-description']:'';
                $arrDataVendorInformation['Vendor-website'] = (isset($data['Vendor-website']))? $data['Vendor-website']:'';
                $arrDataVendorInformation['Vendor-countrycode'] = (isset($data['Vendor-countrycode']))? $data['Vendor-countrycode']:'';
                $arrDataVendorInformation['Vendor-phone'] = (isset($data['Vendor-phone']))? $data['Vendor-phone']:'';
                $arrDataVendorInformation['Vendor-email'] = (isset($data['Vendor-email']))? $data['Vendor-email']:'';
                $VendorInformationDataJson =  $this->helper->arrayToJson($arrDataVendorInformation);                
                $verificationInfo = $this->verificationInfoFactory->create()->load($detail_id);            
                $verificationInfo->setVendorData($VendorInformationDataJson);
                $verificationInfo->setApproval(0);
                $verificationInfo->setStatus(\Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING);                    
                $verificationInfo->save();
		    }else if($typ_id == \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_ADDRESS){
		        /**  vendor Verification Data : Vendor Address **/
			    $dataGroupId =  \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_ADDRESS;	
                $arrDataVendorAddress = array();
                /*$arrDataVendorAddress['Vendor-address-line-1'] = $data['Vendor-address-line-1'];
                $arrDataVendorAddress['Vendor-address-line-2'] = $data['Vendor-address-line-2'];
                $arrDataVendorAddress['Vendor-city'] = $data['Vendor-city'];
                $arrDataVendorAddress['Vendor-state'] = $data['Vendor-state'];
                $arrDataVendorAddress['Vendor-postcode'] = $data['Vendor-postcode'];
                $arrDataVendorAddress['Vendor-lat'] = $data['lat'];
                $arrDataVendorAddress['Vendor-lng'] = $data['lng'];*/
                $arrDataVendorAddress['Vendor-address-line-1'] = (isset($data['Vendor-address-line-1']))? $data['Vendor-address-line-1']:'';
                $arrDataVendorAddress['Vendor-address-line-2'] = (isset($data['Vendor-address-line-2']))? $data['Vendor-address-line-2']:'';
                $arrDataVendorAddress['Vendor-city'] = (isset($data['Vendor-city']))? $data['Vendor-city']:'';
                $arrDataVendorAddress['Vendor-state'] = (isset($data['Vendor-state']))? $data['Vendor-state']:'';
                $arrDataVendorAddress['Vendor-postcode'] = (isset($data['Vendor-postcode']))? $data['Vendor-postcode']:'';
                $arrDataVendorAddress['Vendor-lat'] = (isset($data['lat']))? $data['lat']:'';
                $arrDataVendorAddress['Vendor-lng'] = (isset($data['lng']))? $data['lng']:'';
                $VendorAddressDataJson =  $this->helper->arrayToJson($arrDataVendorAddress);            
                $verificationInfo = $this->verificationInfoFactory->create()->load($detail_id);            
                $verificationInfo->setVendorData($VendorAddressDataJson);
                $verificationInfo->setApproval(0);
                $verificationInfo->setStatus(\Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING);            
                $verificationInfo->save();
		    } else if($typ_id == \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_CONTACT){
			     /** vendor Verification Data : Vendor Contact **/
			     
		        $dataGroupId =  \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_Vendor_CONTACT;
                $arrDataVendorContact = array();
                //$arrDataVendorContact['contact-firstname'] = $data['contact-firstname'];
                //$arrDataVendorContact['contact-lastname'] = $data['contact-lastname'];
                $arrDataVendorContact['contact-name'] = (isset($data['contact-name']))? $data['contact-name']:'';                
                $arrDataVendorInformation['contact-countrycode'] = (isset($data['contact-countrycode']))? $data['contact-countrycode']:'';
                $arrDataVendorContact['contact-phone'] = (isset($data['contact-phone']))? $data['contact-phone']:'';
                $arrDataVendorContact['contact-email'] = (isset($data['contact-email']))? $data['contact-email']:'';
                $VendorContactDataJson =  $this->helper->arrayToJson($arrDataVendorContact);            
                $verificationInfo = $this->verificationInfoFactory->create()->load($detail_id);
                $verificationInfo->setVendorData($VendorContactDataJson);
                $verificationInfo->setApproval(0);
                $verificationInfo->setStatus(\Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING);                    
                $verificationInfo->save();
		    }else if($typ_id == \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS){            
                /** vendor Verification Data : Vendor Certificates and Docs **/                
			    $dataGroupId =  \Vendor\VendorsVerification\Model\Source\InfoGroup::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS;	
                $arrDataVendorCertDocs = array();
                /*$arrDataVendorCertDocs['vendorregistcert'] = $data['vendorregistcert'][0];
                $arrDataVendorCertDocs['vendoradditionaldocs'] = $data['vendoradditionaldocs'];*/
                $arrDataVendorCertDocs['vendorregistcert'] = (isset($data['vendorregistcert'][0]))? $data['vendorregistcert'][0]:'';
                $arrDataVendorCertDocs['vendoradditionaldocs'] = (isset($data['vendoradditionaldocs']))? $data['vendoradditionaldocs']:'';
                $VendorCertificatesDataJson =  $this->helper->arrayToJson($arrDataVendorCertDocs);            
                $verificationInfo = $this->verificationInfoFactory->create()->load($detail_id);                        
                $verificationInfo->setVendorData($VendorCertificatesDataJson);
                $verificationInfo->setApproval(0);
                $verificationInfo->setStatus(\Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING);                    
                $verificationInfo->save();
		    }            
         $this->_messageManager->addSuccess(__(" Verification Data has been successfully updated."));
         return $this->_redirect('vendorverification/verification/index/');		
                     
       }catch (\Magento\Framework\Exception\LocalizedException $e) {                
                $this->messageManager->addError($e->getMessage());                
                
        } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());                
                
        }
        return $this->_redirect('vendorverification/verification/index/');		
        
        
    }
    
  private function getDataGroupLabel($datagroupId){
		$statusOptions = $this->verificationDataGrop->getAllOptions();
		$label = '';		
		$arrStatus= array();
		foreach($statusOptions as $key=>$option){			
		    $arrStatus[$option['value']] = (string) $option['label'];
		}
		
		$label = (string) $arrStatus[$datagroupId];
		return $label;
	}
	    
}
