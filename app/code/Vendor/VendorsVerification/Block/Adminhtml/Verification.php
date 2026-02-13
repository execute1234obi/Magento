<?php

namespace Vendor\VendorsVerification\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Directory\Model\Config\Source\Country as CountrySource;
use Vnecoms\Vendors\Model\VendorFactory; // Wapas Factory use kar rahe hain
use Vnecoms\VendorsConfig\Helper\Data as ConfigHelper;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Helper\Data as ModuleHelper;

class Verification extends Template
{
    protected $_coreRegistry;
    protected $vendorFactory;
    protected $countrySource;
    protected $priceHelper;
    protected $helper;
    protected $_vendorVerificationFactory;
    protected $_verificationInfoFactory;
    protected $_configHelper;
    protected $currencyFactory;
    protected $timezone;
    protected $formKey;
    protected $eavConfig;
    protected $statusOptions;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        VendorFactory $vendorFactory, // Repository ki jagah Factory
        CountrySource $countrySource,
        PriceHelper $priceHelper,
        ModuleHelper $helper,
        VendorVerificationFactory $vendorsVerificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        ConfigHelper $configHelper,
        EavConfig $eavConfig,
        CurrencyFactory $currencyFactory,
        TimezoneInterface $timezone,
        FormKey $formKey,
        \Vendor\VendorsVerification\Model\Source\Status $statusOptions,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->vendorFactory = $vendorFactory;
        $this->countrySource = $countrySource;
        $this->priceHelper = $priceHelper;
        $this->helper = $helper;
        $this->_vendorVerificationFactory = $vendorsVerificationFactory;
        $this->_verificationInfoFactory = $verificationInfoFactory;
        $this->_configHelper = $configHelper;
        $this->eavConfig = $eavConfig;
        $this->currencyFactory = $currencyFactory;
        $this->timezone = $timezone;
        $this->formKey = $formKey;
        $this->statusOptions = $statusOptions;
        parent::__construct($context, $data);
    }

    /**
     * Get Current Vendor using Factory load
     */
    public function getVendor()
    {
        $verification = $this->getVendorVerification();
        if (!$verification || !$verification->getVendorId()) {
            return null;
        }
        // Purana tareeka wapas laya gaya compilation error fix karne ke liye
        return $this->vendorFactory->create()->load($verification->getVendorId());
    }

    public function getVendorVerification()
    {
        return $this->_coreRegistry->registry('vendor_verification');
    }

    public function getVerificationSectionData($verificationId, $sectionId)
    {
        $collection = $this->_verificationInfoFactory->create()->getCollection()
            ->addFieldToFilter('verification_id', ['eq' => $verificationId])
            ->addFieldToFilter('datagroup_id', ['eq' => $sectionId]);
        return $collection->getFirstItem();
    }

    public function convertVerificationData($jsonDataStr)
    {
        if (!$jsonDataStr) return null;
        return $this->helper->jsonToArray($jsonDataStr);
    }

    public function getVendorStoreData($path)
    {
        $vendor = $this->getVendor();
        if (!$vendor) return null;
        return $this->_configHelper->getVendorConfig($path, $vendor->getId(), $this->getStoreId());
    }

    // public function getEditActionUrl($verificationId, $detailId, $typeId, $actionStatus = 0)
    // {
    //     $queryParams = ['id' => $verificationId, 'dtl_id' => $detailId, 'typ_id' => $typeId, 'actstatus' => $actionStatus];
    //     return $this->getUrl('*/*/update/', ['_current' => true, '_query' => $queryParams]);
    // }
    public function getEditActionUrl($verificationId, $detailId, $typeId, $actionStatus = 0)
{
    return $this->getUrl(
        'vendorverification/index/update',
        [
            'id'        => $verificationId,
            'dtl_id'    => $detailId,
            'typ_id'    => $typeId,
            'actstatus' => $actionStatus
        ]
    );
}


    public function getDeleteActionUrl($verificationId)
    {
        return $this->getUrl('*/*/delete/', ['id' => $verificationId]);
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    public function getCommentsViewUrl()
    {
        return $this->getUrl('vendorverification/ajax/viewcomment');
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getCountries()
    {
        $options = $this->countrySource->toOptionArray();
        array_unshift($options, ['value' => 'all', 'label' => __('All Country')]);
        return $options;
    }

    public function getCountriByCode($countryCode)
    {
        $countries = $this->countrySource->toOptionArray();
        foreach ($countries as $country) {
            if ($country['value'] == $countryCode) return $country['label'];
        }
        return ($countryCode == 'all') ? __('All Country') : $countryCode;
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
        $code = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return $this->currencyFactory->create()->load($code)->getCurrencySymbol();
    }

    public function getFormatedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    public function getFormatedDate($date)
    {
        return $this->timezone->date(new \DateTime($date))->format('F j, Y');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getStatusLabel($status)
    {
        $options = $this->statusOptions->toOptionArray();
        $label = __('Undefined');
        foreach ($options as $opt) {
            if ($opt['value'] == $status) $label = $opt['label'];
        }

        $colors = [
            \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS_PENDING => '#b5b69c',
            \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__RESUBMIT => '#339af0',
            \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__REJECTED => '#ff2500',
            \Vendor\VendorsVerification\Model\Source\Status::VENDOR_VERIFICATION_STATUS__VERIFIED => '#82c91e'
        ];
        $bgColor = $colors[$status] ?? '#ccc';

        return '<label style="padding:5px; font-weight:bold; background-color:'.$bgColor.'; color:#fff;">'.$label.'</label>';
    }

    public function getvendorVerificationStatusLabel($status)
    {
        $data = [
            0 => ['label' => __('Not Verified'), 'color' => '#ff8c00'],
            1 => ['label' => __('Verified'), 'color' => '#82c91e'],
            9 => ['label' => __('Expired'), 'color' => '#ff2500']
        ];
        $res = $data[$status] ?? ['label' => __('Undefined'), 'color' => '#ccc'];
        return '<label style="padding:5px; font-weight:bold; background-color:'.$res['color'].'; color:#fff;">'.$res['label'].'</label>';
    }

    public function getPaymentStatusLabel($status)
    {
        $color = ($status == 1) ? '#82c91e' : '#ff2500';
        $text = ($status == 1) ? __('Paid') : __('Not Paid');
        return '<label style="padding:5px; font-weight:bold; background-color:'.$color.'; color:#fff;">'.$text.'</label>';
    }
}