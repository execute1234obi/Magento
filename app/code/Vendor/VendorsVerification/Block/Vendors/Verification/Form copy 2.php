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
        return $this->_vendorSession->getVendor();
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
      $logger = new \Zend_Log();
        $logger->addWriter(new \Zend_Log_Writer_Stream(BP . '/var/log/vendor_pdf_debug.log'));
        $logger->info('✅ getCertificateFilesJson() called for Vendor PDF');
    $vendor = $this->getVendor();
    if (!$vendor || !$vendor->getId()) {
        return '[]';
    }

    $files = $vendor->getData('certificate'); 
    if (!$files) return '[]';

    $fileList = array_map('trim', explode(',', $files));
    return json_encode($this->populateAttachmentsFromUrls($fileList) ?: []);
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

    return json_encode($this->populateAttachmentsFromUrls([$logo]) ?: []);
}

/**
 * Prepare file data from full URLs for Magento UI Uploader component
 * @param array $urls
 * @return array
 */
public function populateAttachmentsFromUrls($urls)
{
    $logger = new \Zend_Log();
        $logger->addWriter(new \Zend_Log_Writer_Stream(BP . '/var/log/vendor_pdf_debug.log'));
        $logger->info('✅ populateAttachmentsFromUrls() called for Vendor PDF');
    $arrUploads = [];
    $mediaUrl = $this->getMediaUrl();
    $mediaDir = $this->mediaDirectory->getAbsolutePath();
    $imageTypes = ['image/jpeg','image/jpg','image/png','image/gif'];

    foreach ($urls as $url) {
        if (!$url) continue;

        // Strip base media URL if present
        if (strpos($url, $mediaUrl) !== false) {
            $relativePath = str_replace($mediaUrl, '', $url);
        } else {
            // If full URL not starting with media URL, attempt to parse path
            $parsed = parse_url($url, PHP_URL_PATH);
            $relativePath = ltrim(str_replace('/pub/media/', '', $parsed), '/');
        }

        // Check if file exists
        if (!$this->mediaDirectory->isExist($relativePath)) continue;

        $fileAbsolutePath = $mediaDir . $relativePath;
        $fileSize = $this->mediaDirectory->stat($relativePath)['size'];
        
        try {
            $mimeType = $this->mime->getMimeType($fileAbsolutePath);
        } catch (\Exception $e) {
            $mimeType = 'application/octet-stream';
        }

        $arrUploads[] = [
            'name' => basename($relativePath),
            'type' => $mimeType,
            'size' => $fileSize,
            'url'  => $mediaUrl . $relativePath,
            'file' => $relativePath,
            'previewType' => in_array($mimeType, $imageTypes) ? 'image' : 'file'
        ];
    }
$this->logger->info("🟢 Updated PDF: " . json_encode($arrUploads));
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
