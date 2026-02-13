<?php

namespace Vendor\VendorsVerification\Block\Vendors\Verification;

use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\Vendors\Model\Source\RegisterType;
use Magento\Framework\Pricing\Helper\Data as priceHelper;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

class Form extends \Magento\Framework\View\Element\Template
{   
    /**
     * @var \Vnecoms\Vendors\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;    

    /**
     * Config helper
     *
     * @var \Vnecoms\VendorsConfig\Helper\Data
     */
    protected $_configHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $priceHelper;
    protected $_storeManager;
    protected $timezone;
    protected $countries;
    protected $currencyFactory;
    private $storeConfig;
    protected $VendorTypesOptions;
    protected $eavConfig;
    private $mediaDirectory;
    protected $filesystem;
    private $mime;
    protected $logger;

    public function __construct(
         \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        VendorSession $vendorSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CountryCollectionFactory $countries,
        \Vendor\VendorsVerification\Model\Source\VendorTypes $Vendortypes,
        priceHelper $priceHelper,
        \Vnecoms\VendorsConfig\Helper\Data $configHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,        
        \Magento\Eav\Model\Config $eavConfig,
        Filesystem $filesystem,
        Mime $mime,
        array $data = []
    ) {        
        parent::__construct($context, $data);

        $this->_vendorHelper = $vendorHelper;
        $this->_vendorSession = $vendorSession;
        $this->_coreRegistry = $coreRegistry;        
        $this->priceHelper = $priceHelper;
        $this->currencyFactory = $currencyFactory;
        $this->_storeManager  = $storeManager;       
        $this->timezone = $timezone;
        $this->countries = $countries;
        $this->VendorTypesOptions = $Vendortypes;
        $this->_configHelper = $configHelper;
        $this->eavConfig = $eavConfig;
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->logger = $logger;

        $this->mediaDirectory = $filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );
    }

public function getVendor()
{
    try {
        $vendor = $this->_vendorSession->getVendor();
        if ($vendor && $vendor->getId()) {
            return $vendor;
        }
    } catch (\Exception $e) {
        $this->logger->error('Vendor session error: ' . $e->getMessage());
    }
    return null;
}
  

public function getVendorStoreData($path)
{
        $store = $this->getStoreId();
        return $this->_configHelper->getVendorConfig(
            $path,
            $this->getVendor()->getId(),
            $store
        );
}
    /**
 * Get Vendor Address Data
 *
 * @return array
 */
public function getVendorAddress()
{
    $vendor = $this->getVendor();
    if (!$vendor) {
        return [
            'street'   => '',
            'city'     => '',
            'state'    => '',
            'postcode' => '',
            'country'  => ''
        ];
    }

    return [
        'street'   => (string)$vendor->getData('street'),
        'city'     => (string)$vendor->getData('city'),
        'state'    => (string)$vendor->getData('b_state'),
        'postcode' => (string)$vendor->getData('postcode'),
        'country'  => (string)$vendor->getCountryId()
    ];
}
/**
 * Get Certificate Files in JSON format for UI Uploader
 * @return string
 */
public function getCertificateFilesJson()
{
    //$vendor = $this->getVendor();die();
  $this->logger->info('getCertificateFilesJson called');

    if (!$vendor || !$vendor->getId()) {
        return '[]';
    }

    $files = $vendor->getData('certificate'); 
    if (!$files) return '[]';

    $fileList = array_map('trim', explode(',', $files));
    return json_encode($this->populateAttachements($fileList) ?: []);
}

/**
 * Get Logo Files in JSON format
 * @return string
 */
public function getLogoFilesJson()
{
    $vendor = $this->getVendor();
    if (!$vendor) return '[]';

    $logo = $vendor->getData('upload_logo');
    if (!$logo) return '[]';

    return json_encode($this->populateAttachements([$logo]) ?: []);
}

/**
 * Prepare file data from full URLs for Magento UI Uploader component
 * @param array $urls
 * @return array
 */
/**
 * PHTML file is function ko call kar rahi hai
 */
public function populateAttachements($urls)
{
    // Yahan manual logger lagayein confirm karne ke liye
    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/vendor_pdf_debug.log');
    $logger = new \Zend_Log();
    $logger->addWriter($writer);
    $logger->info('Function started with URLs: ' . json_encode($urls));

    $arrUploads = [];
    $mediaUrl = $this->getMediaUrl();
    
    foreach ($urls as $url) {
        if (!$url) continue;
        
        // Aapka baki logic yahan aayega...
        
        $arrUploads[] = [
            'name' => basename($url),
            'type' => 'file', // Temporary test ke liye
            'size' => 100,
            'url'  => $url,
            'file' => $url
        ];
    }

    $logger->info('Final Data: ' . json_encode($arrUploads));
    return $arrUploads;
}
public function getPostActionUrl()
{
        return $this->getUrl('*/*/postVerification');
}

public function getBackUrl()
{
        return $this->getUrl('*/vendors/index');
}    

public function getStoreId()
{
        return $this->_storeManager->getStore()->getId();
}   

public function getCountries()
{
        $arrCountries = [];
        $collection = $this->countries->create()->loadByStore();

        foreach ($collection as $country) {
            $arrCountries[] = [
                'value' => $country->getCountryId(),
                'label' => $country->getName()
            ];
        }
        return $arrCountries;
} 

public function getMediaUrl()
{
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
}

public function getVendorTypes()
{
       // Database mein attribute_code 'business_type' hai
    $attribute = $this->eavConfig->getAttribute('vendor', 'business_type');
    return $attribute->getSource()->getAllOptions();
}

public function getVendorCategories()
{
       // Database mein attribute_code 'business_category' hai
    $attribute = $this->eavConfig->getAttribute('vendor', 'business_category');
    return $attribute->getSource()->getAllOptions();
}

public function getCurrencySymbol()
{
        $currentCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->create()->load($currentCurrency);
        return $currency->getCurrencySymbol();
}

public function getConfig($config_path, $storeCode = null)
{
        return $this->_configHelper->getConfig($config_path, $storeCode);
}

   
}
