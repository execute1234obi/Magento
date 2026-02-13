<?php
namespace Vendor\VendorsVerification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vnecoms\Vendors\Model\VendorFactory;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Vnecoms\VendorsConfig\Helper\Data as VendorConfigHelper;

class Data extends AbstractHelper
{
	
	const XML_PATH_VERIFICATION_EXPIRY_NOTIFICATION_EMAIL    = 'Vendor/bendorsVerification/expiry_notification';
	const XML_PATH_EMAIL_SENDER                               = 'Vendor_vendorverification/config/sender_email_identity';
	
	private $httpContext;
    
    protected $storeManager;    
    
    protected $customer;
    
    protected $_customerFactory;
    
     /**
     * @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory
     */
    private $_vendorFactory;
    
    //protected $_addressFactory;
    
    protected $context;
    
    protected $_scopeConfig;    
    
    //protected $_inlineTranslation;
    
    //protected $_transportBuilder;
    
    protected $_configPath;
    
    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    protected $_urlBuilder;
    
    /**
     * @var \Vendor\VendorsVerification\Helper\Email
     */
    protected $_emailHelper;
    
    
    /**
     * @var SerializerInterface
     */
    protected $serializer;   
	
	/**
     * @var \Vendor\VendorsVerification\Model\VendorVerification
     */
    protected $_vendorVerificationFactory;
    
    /**
     * @var Country
     */
    public $countryFactory;
       

    /**
     * @var array
     */
    protected $jsLayout;
    /**
     * @var DateTime
     */
    protected $date;

      /**
     * @var VendorConfigHelper
     */
    protected $_configHelper;
	 
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        //\Magento\Eav\Model\Config $eavConfig,
        //Magento\Customer\Model\Session $customer,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        VendorFactory $vendorFactory,
        //\Magento\Customer\Model\AddressFactory $addressFactory,        
       // \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        //\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        //\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,        
        SerializerInterface $serializer,
        VendorVerificationFactory $vendorVerificationFactory,
        //\Vnecoms\VendorsConfig\Helper\Data $configHelper,
        \Magento\Framework\Url $urlBuilder,
        \Vnecoms\Vendors\Helper\Email $emailHelper,
        CountryFactory $countryFactory  ,
         DateTime $date  ,
          VendorConfigHelper $configHelper    
    ) 
    {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        //$this->_eavConfig = $eavConfig;        
        //$this->_transportBuilder = $transportBuilder;
        //$this->customer = $customer;
        //$this->_addressFactory = $addressFactory;        
        //$this->_inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;        
        $this->_customerFactory = $customerFactory; 
        $this->_vendorFactory = $vendorFactory;       
        $this->date = $date;        
        $this->_scopeConfig = $scopeConfig;	
        $this->context = $context;
        $this->serializer = $serializer;
        $this->_vendorVerificationFactory = $vendorVerificationFactory;
        $this->_configHelper = $configHelper;
        $this->_urlBuilder = $urlBuilder;
        $this->_emailHelper = $emailHelper;
        $this->countryFactory = $countryFactory;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
    }
     public function getCurrentDate()
    {
        return $this->date->gmtDate();
    }
   public function getConfigValue($path)
    {
        return $this->_configHelper->getVendorConfig($path);
    }

	public function arrayToJson($data)
    {
        return $this->serializer->serialize($data);
    }

    public function jsonToArray($data)
    {
        return $this->serializer->unserialize($data);
    }
    
    
    /**
     * country full name
     *
     * @return string
     */
    public function getCountryName($countryId): string
    {
        $countryName = '';
        $country = $this->countryFactory->create()->loadByCode($countryId);
        if ($country) {
            $countryName = $country->getName();
        }
        return $countryName;
    }
    
    
    public function getCustomerById($customerId){
		return $this->_customerFactory->create()->load($customerId);
	}
	
      
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
    
    public function getVendorById($vendorId){
		return $this->_vendorFactory->create()->load($vendorId);
	}
	
	public function getStoreName($vendorId){
		
	}
    
   public function isAlreadyApplied($vendorId)
{
    $collection = $this->_vendorVerificationFactory->create()
        ->getCollection()
        ->addFieldToFilter('main_table.vendor_id', $vendorId)
        ->addFieldToFilter('main_table.is_active', 1)
        ->addFieldToFilter('status', ['neq' => 5]);
    $count=   $collection->getSize(); 
$isExist = ($count>0)?true:false;
		return $isExist;
//return $collection->getSize() > 0;
}

	
	public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
    
    public function IsVerifiedVendor($vendorId){		
		$isVerified =  false;		
		//$today = (new \DateTime())->format('Y-m-d');
		$today  =  $this->date->date('Y-m-d');
		$vendorverificationcollection = $this->_vendorVerificationFactory->create()
		->getCollection()
		->addFieldToFilter('vendor_id',array('eq' => $vendorId))
		->addFieldToFilter('to_date',array('gteq' => $today))
		->addFieldToFilter('is_verified',array('eq' => 1));
		$count = $vendorverificationcollection->getSize();		
		//$isVerified = ($count)? true:false;
		$vendor = $this->_vendorFactory->create()->load($vendorId); 
		if($vendor->getIsVerified() && $count>0){		
			$isVerified =  true;
		}
		/*if(!$isExist){
			return $isVerified;
		}		
		$vendorVerification = $vendorverificationcollection->getFirstItem();
		if($vendorVerification->getIsVerified() &&   $vendorVerification->getToDate()>=$today){
			$isVerified =  true;
		}*/
		return $isVerified;
	}

    
    /**
     * Send SellerVerification Expiry Notification Email To Vendor
     *
     * @param \Vnecoms\Vendors\Model\Vendor $vendor
     */
    public function sendExpiryNotificationEmail(        
        \Vendor\VendorsVerification\Model\VendorVerification $vendorVerification,
        \Vnecoms\Vendors\Model\Vendor $vendor
    ) {
        $vendorEmail = $vendor->getEmail();
        $verifiationReviewUrl = $this->_urlBuilder->getUrl('vendorverification/verification/index/');        
        $this->_emailHelper->sendTransactionEmail(
            self::XML_PATH_VERIFICATION_EXPIRY_NOTIFICATION_EMAIL,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            self::XML_PATH_EMAIL_SENDER,
            $vendorEmail,
            ['vendor' => $vendor,"vendorVerification" => $vendorVerification, 'verifiationReviewUrl' => $verifiationReviewUrl]
        );
    }
        
    
    public function getContentCss()
    {
        $css =  $this->_assetRepo->getUrl(
            'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
        );
        return $css;
    }
}    
