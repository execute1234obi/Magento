<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_MEMBERSHIP_PAGE_TITLE             = 'vendors/membership/page_title';
    const XML_MEMBERSHIP_PAGE_KEYWORDS          = 'vendors/membership/meta_keyword';
    const XML_MEMBERSHIP_PAGE_DESCRIPTION       = 'vendors/membership/meta_description';
    const XML_MEMBERSHIP_COLOR_PACKAGE          = 'vendors/membership/color_package';
    const XML_PATH_EXPIRY_NOTIFICATION_EMAIL    = 'vendors/membership/expiry_notification';
    const XML_PATH_EXPIRY_DAY_BEFORE            = 'vendors/membership/expiry_day_before';
    const XML_PATH_EMAIL_SENDER                 = 'vendors/membership/sender_email_identity';
    const XML_PATH_PAYMENT_RESTRICT             = 'vendors/membership/payment_restrict_method';

    const XML_PATH_DEFAULT_EXPIRY_DAY           = 'vendors/membership/default_expiry_day';
    const XML_PATH_EXPIRY_ACTION                = 'vendors/membership/expiry_action';
    const XML_PATH_EXPIRY_VENDOR_GROUP          = 'vendors/membership/enpiry_vendor_group';
    const XML_PATH_DEFAULT_VENDOR_GROUP         = 'vendors/create_account/default_group';
    
    
    /**
     * @var \Vnecoms\Vendors\Helper\Email
     */
    protected $_emailHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    
    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    protected $_urlBuilder;
    
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Vnecoms\Vendors\Helper\Email $emailHelper
     * @param \Magento\Framework\Url $urlBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vnecoms\Vendors\Helper\Email $emailHelper,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->_emailHelper = $emailHelper;
        $this->_localeDate = $localeDate;
        $this->_urlBuilder = $urlBuilder;
    }
    
    /**
     * Send Expiry Notification Email To Vendor
     *
     * @param \Vnecoms\Vendors\Model\Vendor $vendor
     */
    public function sendExpiryNotificationEmail(
        \Vnecoms\Vendors\Model\Vendor $vendor
    ) {
        $vendorEmail = $vendor->getEmail();
        $vendor->setData(
            'expiry_date',
            $this->_localeDate->formatDate(
                $vendor->getExpiryDate(),
                \IntlDateFormatter::MEDIUM
            )
        );
        $pricingUrl = $this->_urlBuilder->getUrl('vendorsmembership');
        
        $this->_emailHelper->sendTransactionEmail(
            self::XML_PATH_EXPIRY_NOTIFICATION_EMAIL,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            self::XML_PATH_EMAIL_SENDER,
            $vendorEmail,
            ['vendor' => $vendor, 'pricing_url' => $pricingUrl]
        );
    }

    /**
     * The number of days before seller account is expired which notification email will be sent
     *
     * @return int
     */
    public function getExpiryDayBefore(){
        return $this->scopeConfig->getValue(self::XML_PATH_EXPIRY_DAY_BEFORE);
    }

    /**
     * The number of days before seller account is expired which notification email will be sent
     *
     * @return int
     */
    public function getPaymentMethodRetricts(){
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_RESTRICT);
    }

    /**
     * Get Membership page title.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->scopeConfig->getValue(self::XML_MEMBERSHIP_PAGE_TITLE);
    }

    /**
     * Get Membership page title.
     *
     * @return string
     */
    public function getPageKeywords()
    {
        return $this->scopeConfig->getValue(self::XML_MEMBERSHIP_PAGE_KEYWORDS);
    }

    /**
     * Get Membership page title.
     *
     * @return string
     */
    public function getPageDescription()
    {
        return $this->scopeConfig->getValue(self::XML_MEMBERSHIP_PAGE_DESCRIPTION);
    }

    /**
     * Get Package Color.
     * 
     * @param int $package
     */
    public function getPackageColor($package)
    {
        return $this->scopeConfig->getValue(self::XML_MEMBERSHIP_COLOR_PACKAGE.$package);
    }
    
    /**
     * Get default expiry day
     * 
     * @return int
     */
    public function getDefaultExpiryDay(){
        return (int) $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_EXPIRY_DAY);
    }
    
    /**
     * Get Expiry Action
     * 
     * @return string
     */
    public function getExpiryAction(){
        return $this->scopeConfig->getValue(self::XML_PATH_EXPIRY_ACTION);
    }
    
    /**
     * Get Expiry vendor group Id
     * 
     * @return int
     */
    public function getExpiryVendorGroup(){
        return (int)$this->scopeConfig->getValue(self::XML_PATH_EXPIRY_VENDOR_GROUP);
    }
    
    /**
     * Get default vendor group Id
     *
     * @return int
     */
    public function getDefaultVendorGroup(){
        return (int)$this->scopeConfig->getValue(self::XML_PATH_DEFAULT_VENDOR_GROUP);
    }
}
