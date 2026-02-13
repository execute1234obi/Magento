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

    public function __construct(
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
        $attribute = $this->eavConfig->getAttribute('vendor', 'Vendor_type');
        return $attribute->getSource()->getAllOptions();
    }

    public function getVendorCategories()
    {
        $attribute = $this->eavConfig->getAttribute('vendor', 'Vendor_category');
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

    public function populateAttachements($data)
    {
        $attachmentPath = $this->mediaDirectory->getAbsolutePath();
        $arrImageTypes = ['image/jpeg','image/jpg','image/png','image/gif'];

        if (count($data) <= 0) {
            return null;
        }

        $arrUploads = [];

        foreach ($data as $key => $value) {
            $fileAbsolutePath = $attachmentPath . $value;
            $fileRelativePath = $this->getMediaUrl() . $value;
            $fileSize = $this->mediaDirectory->stat($value)['size'];    
            $result = $this->mime->getMimeType($fileAbsolutePath);
            $resultArr = explode("/", $result);
            $type = $resultArr[1];
            $fileiconFolder = 'porto/';

            if (!in_array($type, $arrImageTypes)) {
                $fileNamePart = explode('.', $value);
                $type = $fileNamePart[count($fileNamePart) - 1];

                switch (strtolower($type)) {
                    case 'pdf':
                        $fileRelativePath = $this->getMediaUrl() . $fileiconFolder . 'pdf.png';
                        break;
                    case 'doc':
                        $fileRelativePath = $this->getMediaUrl() . $fileiconFolder . 'vendor-doc.png';
                        break;
                    case 'docx':
                        $fileRelativePath = $this->getMediaUrl() . $fileiconFolder . 'doc.png';
                        break;
                    default:
                        break;
                }
            }

            $arrUploads[] = [
                'name' => $value,
                'type' => 'image',
                'error' => 0,
                'size' => $fileSize,
                'path' => $fileAbsolutePath,
                'file' => $value,
                'url' => $fileRelativePath,
                'previewType' => 'image',
                'id' => rand(1, 9999999)
            ];
        }

        return $arrUploads;
    }
}
