<?php

namespace Vendor\VendorsVerification\Block\Vendors\Verification\View;

use Magento\Framework\View\Element\Template;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationDataFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;

class Verification extends Template
{
    protected $vendorSession;
    protected $vendorVerificationFactory;
    protected $verificationDataFactory;
    protected $countryCollectionFactory;
    protected $eavConfig;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        VendorSession $vendorSession,
        VendorVerificationFactory $vendorVerificationFactory,
        VerificationDataFactory $verificationDataFactory,
        CountryCollectionFactory $countryCollectionFactory,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->vendorSession = $vendorSession;
        $this->vendorVerificationFactory = $vendorVerificationFactory;
        $this->verificationDataFactory = $verificationDataFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /* ==============================
       VENDOR
    ============================== */
/* ==============================
       VENDOR - Fixed Variable Name
    ============================== */
    public function getVendor()
    {
        try {
            // Fix: Changed _vendorSession to vendorSession
            $vendor = $this->vendorSession->getVendor();
            if ($vendor && $vendor->getId()) {
                return $vendor;
            }
        } catch (\Exception $e) {
            // Logger check
            if($this->_logger) $this->_logger->error('Vendor session error: ' . $e->getMessage());
        }
        return null;
    }

    /* ==============================
       VERIFICATION (MASTER) - Added Real ID Check
    ============================== */
  public function getVendorVerification()
    {
        $vendor = $this->getVendor();

        if (!$vendor || !$vendor->getId()) {
            return null;
        }

        return $this->vendorVerificationFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('main_table.vendor_id', $vendor->getId())
            ->getFirstItem();
    }

    // public function isActiveVerificationExit()
    // {
    //     $verification = $this->getVendorVerification();
    //     return ($verification && $verification->getId());
    // }
    public function isActiveVerificationExit()
{
    try {
        $verification = $this->getVendorVerification();
        
        // 1. Check karein ki $verification null toh nahi hai
        // 2. Check karein ki ye ek Object hai (Collection nahi)
        // 3. Phir check karein ki uski ID exist karti hai
        if ($verification && is_object($verification) && $verification->getId()) {
            return true;
        }
    } catch (\Exception $e) {
        // Agar koi error aaye (jaise DB table missing), toh crash na ho
        return false;
    }
    
    return false;
}

    /* ==============================
       VERIFICATION DATA (SECTIONS)
       ✔ OLD getVerificationSectionData()
    ============================== */

    /**
     * @param int $verificationId
     * @param string $groupCode (business_address, business_contact, documents etc.)
     */
    public function getVerificationSectionData($verificationId, $groupCode)
    {
        if (!$verificationId || !$groupCode) {
            return null;
        }

        return $this->verificationDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('verification_id', $verificationId)
            ->addFieldToFilter('info_group', $groupCode)
            ->getFirstItem();
    }

    /**
     * Safe JSON → Array conversion
     */
    public function convertVerificationData($json)
    {
        if (!$json) {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    /* ==============================
       VERIFICATION COMPLETION CHECK
       (Add to Cart dependency)
    ============================== */

    public function isVerificationCompleted()
    {
        $verification = $this->getVendorVerification();
        if (!$verification || !$verification->getId()) {
            return false;
        }

        $requiredSections = [
            'business_address',
            'business_contact',
            'business_documents'
        ];

        foreach ($requiredSections as $section) {
            $data = $this->getVerificationSectionData($verification->getId(), $section);
            if (!$data || !$data->getId()) {
                return false;
            }
        }

        return true;
    }

    /* ==============================
       COUNTRY
    ============================== */

    public function getCountries()
    {
        $countries = [];
        $collection = $this->countryCollectionFactory->create()->loadByStore();

        foreach ($collection as $country) {
            $countries[] = [
                'value' => $country->getCountryId(),
                'label' => $country->getName()
            ];
        }
        return $countries;
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
       VENDOR ATTRIBUTES
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
}
