<?php

namespace Vendor\VendorsVerification\Block\Vendors\Verification\View;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
//use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;
use Vendor\VendorsVerification\Model\VerificationCommentFactory;
//use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Serialize\Serializer\Json;

class Verification extends Template
{
    /**
     * @var VendorSession
     */
    protected $vendorSession;

    /**
     * @var VendorVerificationFactory
     */
    protected $vendorVerificationFactory;

    protected $verificationInfoFactory; // ✅ ADD THIS

     protected $statusOptions;
    /**
     * @var CountryCollectionFactory
     */
    //protected $countryCollectionFactory;
    
    /**
     * @var EavConfig
     */
    protected $eavConfig;
    

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /** @var \Magento\Framework\Pricing\Helper\Data */
    protected $priceHelper;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    protected $timezone;

    /**
 * @var VerificationDataFactory
 */
protected $verificationDataFactory;

/**
 * @var VerificationCommentFactory
 */
protected $verificationCommentFactory;

/**
 * @var \Magento\Directory\Model\Config\Source\Country
 */
protected $countrySource;

protected Json $jsonSerializer;

    public function __construct(
        Template\Context $context,
        VendorSession $vendorSession,
        VendorVerificationFactory $vendorVerificationFactory,
       // CountryCollectionFactory $countryCollectionFactory,
       CountryCollectionFactory $countryCollectionFactory,
       Country $countrySource,
         \Vendor\VendorsVerification\Model\VerificationInfoFactory $verificationInfoFactory,
           \Vendor\VendorsVerification\Model\VerificationCommentFactory $verificationCommentFactory,
        EavConfig $eavConfig,
         \Vendor\VendorsVerification\Model\Source\Status $statusOptions,
        \Magento\Framework\Pricing\Helper\Data $priceHelper, // Add this
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        StoreManagerInterface $storeManager,
         ResourceConnection $resource,
          Json $jsonSerializer,
        array $data = []
    ) {
        $this->vendorSession = $vendorSession;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
         $this->verificationCommentFactory = $verificationCommentFactory;
        //$this->countryCollectionFactory = $countryCollectionFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->eavConfig = $eavConfig;
        $this->statusOptions =  $statusOptions;
        $this->storeManager = $storeManager;
         $this->verificationInfoFactory = $verificationInfoFactory; 
        $this->priceHelper = $priceHelper; // Initialize
        $this->timezone = $timezone; // Initialize
         $this->countrySource = $countrySource;
         $this->resource = $resource;
         $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
        
    }

    /* ==============================
       VENDOR
    ============================== */

    public function getVendor()
    {
        return $this->vendorSession->getVendor();
    }

    public function getVendorAttributeValue($attributeCode)
    {
        $vendor = $this->getVendor();
        return $vendor ? $vendor->getData($attributeCode) : null;
    }

    /* ==============================
       VERIFICATION
    ============================== */

    // public function getVendorVerification()
    // {
    //     $vendor = $this->getVendor();

    //     if (!$vendor || !$vendor->getId()) {
    //         return null;
    //     }

    //     return $this->vendorVerificationFactory
    //         ->create()
    //         ->getCollection()
    //         ->addFieldToFilter('main_table.vendor_id', $vendor->getId())
    //         ->getFirstItem();
    // }
/**
 * Get the most relevant verification record
 */
public function getVendorVerification() 
{
  $vendor = $this->vendorSession->getVendor();
    if (!$vendor || !$vendor->getId()) {
        return null;
    }
    $vendorId = $vendor->getId();
    //die("Testing Vendor ID: " . $this->vendorSession->getVendor()->getId());
    // Collection create karein
    $collection = $this->vendorVerificationFactory->create()
        ->getCollection()
        ->addFieldToFilter('vendor_id', $vendorId)
        ->setOrder('verification_id', 'DESC'); // Latest records upar aayenge (31, 27)

    if ($collection->getSize() > 0) {
       //die("Testing Vendor ID: " . $this->vendorSession->getVendor()->getId());
       $firstItem = $collection->getFirstItem();

    // ---- DEBUG START ----
    // Ye code aapko screen par dikhayega ki database se kya mila
    
//    echo "<pre>";
  //  print_r($firstItem->getData()); 
    //echo "</pre>";
    //die("Debugging First Item"); 
    
    // ---- DEBUG END ----

    return $firstItem;
        //return $collection->getFirstItem();
    }

    return null;
}
 public function getEditActionUrl($verificationId,$detailId,$typeId)
    {
		$queryParams = [
            'id' => $verificationId, 
            'dtl_id' => $detailId,
            'typ_id'=> $typeId
        ];
        return $this->getUrl('*/*/updateVerification/',['_current' => true,'_use_rewrite' => true, '_query' => $queryParams]);
    }
    public function isActiveVerificationExit()
    {
        $verification = $this->getVendorVerification();
       // return ($verification && $verification->getId());
       return ($verification && $verification->getId() && $verification->getStatus() != 5);
    }

    /* ==============================
       COUNTRY
    ============================== */

 public function getCountries()
{
    if (!$this->countrySource) {
        return [];
    }

    $countries = $this->countrySource->toOptionArray();
    return is_array($countries) ? $countries : [];
}


    public function getCountryNameByCode($countryCode)
    {
        if (!$countryCode) {
            return '';
        }

        $collection = $this->countryCollectionFactory->create()->loadByStore();
        foreach ($collection as $country) {
            if ($country->getCountryId() == $countryCode) {
                return $country->getName();
            }
        }
        return '';
    }

    /* ==============================
       VENDOR ATTRIBUTE OPTIONS
    ============================== */

    public function getVendorTypes()
    {
        return $this->eavConfig
            ->getAttribute('vendor', 'business_type')
            ->getSource()
            ->getAllOptions();
    }

    public function getVendorCategories()
    {
        return $this->eavConfig
            ->getAttribute('vendor', 'business_category')
            ->getSource()
            ->getAllOptions();
    }

    public function getVendorTypeLabelById($value)
    {
        foreach ($this->getVendorTypes() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return '';
    }

    public function getVendorCategoryLabelById($value)
    {
        foreach ($this->getVendorCategories() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return '';
    }

    /* ==============================
       URLS
    ============================== */

    public function getBackUrl()
    {
        return $this->getUrl('*/vendors/index');
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getVerificationDetail()
{
    if (!$this->getVendorVerification()) {
        return null;
    }

    return $this->vendorVerificationDetailFactory->create()
        ->load($this->getVendorVerification()->getId(), 'verification_id');
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
			    $label = '<label class="label-danger" style="padding:5px;">'.(string) __('Expired').'</label>';
			    break;    
			    
			 default:
			    $label = '<label>Undefined</label>';
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
			    $label = '<label>Undefined</label>';
			    break;              
		}
		return $label;
	}
	
	public function getFormatedPrice($price){
        if ($price === null || $price === '') {
            return (string) __('N/A');
        }

		return $this->priceHelper->currency((float) $price);
	}  
	
	public function getFormatedDate($date){		
        if (!$date) {
            return (string) __('N/A');
        }

        try {
		    return $this->timezone->date(new \DateTime($date))->format('F j, Y');
        } catch (\Exception $e) {
            return (string) __('N/A');
        }
	}

	public function getVerificationMonthsLabel($months)
	{
	    if ($months === null || $months === '') {
	        return (string) __('N/A');
	    }

	    return (string) __('%1 Months', (int) $months);
	}
	
	public function getCommentsCounts($verificationId, $verificationDataId,$dataGroupType){
		$commentsCounts =  0;
		 $commentcollection = $this->verificationCommentFactory->create()
            ->getCollection()
            ->addFieldToFilter('verification_id',array('eq' => $verificationId))
            ->addFieldToFilter('detail_id',array('eq' => $verificationDataId))
            ->addFieldToFilter('datagroup_id',array('eq' => $dataGroupType));	
            
        $commentsCounts = $commentcollection->getSize();
        
        return $commentsCounts;
	}
    /**
 * Get verification data by verification ID & data group
 *
 * @param int $verificationId
 * @param int $dataGroupId
 * @return \Vendor\VendorsVerification\Model\VerificationData|false
 */
// public function getVerificationSectionData($verificationId, $dataGroupId)
// {
//     if (!$verificationId || !$dataGroupId) {
//         return false;
//     }

//     $collection = $this->verificationDataFactory->create()
//         ->getCollection()
//         ->addFieldToFilter('verification_id', (int)$verificationId)
//         ->addFieldToFilter('datagroup_id', (int)$dataGroupId)
//         ->setPageSize(1);

//     $item = $collection->getFirstItem();

//     return $item && $item->getId() ? $item : false;
// }

public function getVerificationSectionData($verificationId, $groupId)
{
    if (!$verificationId || !$groupId) {
        return null;
    }

    $collection = $this->verificationInfoFactory->create()->getCollection();

    // 🔍 DEBUG (temporary)
    // echo $collection->getMainTable(); exit;

    return $collection
         ->addFieldToFilter('verification_id', $verificationId)
         ->addFieldToFilter('datagroup_id', $groupId)
         ->getFirstItem();


        
        //echo $collection->getSelect()->__toString();
       // return  $collection;
//die;
}
public function convertVerificationData($json)
{
    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
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
			    $label = '<label>Undefined</label>';
			    break;              
		}
		return $label;
	}
   public function getCountryByCode($countryCode)
{
    if (!$countryCode) {
        return '';
    }

    try {
        $country = $this->countryInformationAcquirer
            ->getCountryInfo($countryCode);

        return $country->getFullNameLocale();
    } catch (\Exception $e) {
        return '';
    }
}
public function getCommentsViewUrl()
{
    return $this->getUrl('vendorverification/ajax/viewcomment', ['_secure'=>true]);
}
//get varification country list 

public function getAllowedCountries(): array
{
    $configValue = $this->_scopeConfig->getValue(
        'Vendor_vendorverification/config/fees',
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
   // echo '<pre>';
//var_dump($configValue);
//exit;
    if (!$configValue) {
        return [];
    }

    try {
        $rows = $this->jsonSerializer->unserialize($configValue);
    } catch (\Exception $e) {
        return [];
    }

    if (!is_array($rows)) {
        return [];
    }

    $countries = [];

    foreach ($rows as $row) {
        if (!empty($row['countrycode'])) {
            $countries[] = strtoupper(trim($row['countrycode']));
        }
    }
    //print_r($countries);
    //exit;
    return array_values(array_unique($countries));
}


public function isVendorVerified($vendorId = null): bool
{
    //echo $vendorId;
    
    // Agar argument pass kiya gaya hai toh use use karein
    if ($vendorId) {
        // Yahan aapka logic jo database se status check kare
        // Example logic:
        $verification = $this->getVerificationByVendorId($vendorId);
        //print_r($verification->getData('is_verified')); die();
        return ($verification && (int)$verification->getData('is_verified') === 1);
    }

    // Fallback: Purana logic agar argument nahi hai
    $verification = $this->getVendorVerification();
    return ($verification && (int)$verification->getData('is_verified') === 1);
}
/**
 * Get the latest verification record for a specific vendor ID
 * * @param int|string $vendorId
 * @return \Magento\Framework\DataObject|null
 */
public function getVerificationByVendorId($vendorId)
{
 //   echo $vendorId;
    if (!$vendorId) {
        return null;
    }

    try {
        // Factory ka use karke collection create karein
        $collection = $this->vendorVerificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setOrder('verification_id', 'DESC') // Latest record pehle
            ->setPageSize(1); // Memory save karne ke liye sirf 1 record
           // echo "size".$collection->getSize();
            //die();
        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }
    } catch (\Exception $e) {
        // Error logging (optional)
        return null;
    }

    return null;
}
}
