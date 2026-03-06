<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Block\Vendors\Dashboard;

use Vnecoms\VendorsMembership\Model\Product\Type\Membership as MembershipType;

class Membership extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;

    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    
    
    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontendUrl;
    
    /**
     * Constructor.
     * 
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param array $data
     */
    public function __construct(
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Url $frontendUrl,
        array $data = []
    ) {
        $this->_vendorSession = $vendorSession;
        $this->_productFactory = $productFactory;
        $this->_frontendUrl = $frontendUrl;

        parent::__construct($context, $data);
    }

    /**
     * Get current customer.
     *
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor()
    {
        return $this->_vendorSession->getVendor();
    }
    
    /**
     * Get current Group Name.
     *
     * @return string
     */
    public function getCurrentGroupName()
    {   
        return $this->getVendor()->getGroup()->getVendorGroupCode();
    }
    
    /**
     * Get expiry date.
     *
     * @return string
     */
    public function getExpiryDate()
    {
        if(!$this->getVendor()->getExpiryDate()) return __("N/A");
        
        return $this->formatDate(
            $this->getVendor()->getExpiryDate(),
            \IntlDateFormatter::MEDIUM
        );
    }
    
    /**
     * Get customer membership URL.
     *
     * @return string
     */
    public function getCustomerMembershipUrl()
    {
        return $this->getUrl('membership/customer');
    }

    /**
     * Get current package product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentPackageProduct()
    {
        $groupId = $this->getVendor()->getGroupId();
        $product = $this->_productFactory->create();
        $collection = $product->getCollection()
            ->addAttributeToFilter('type_id', MembershipType::TYPE_CODE)
            ->addAttributeToFilter('vendor_membership_group_id', $groupId);
        $product = $collection->getFirstItem();
        $product->load($product->getId());
    
        return $product;
    }
    
    
    /**
     * Get membership pricing page URL.
     *
     * @return string
     */
    public function getRenewUrl()
    {
        return $this->_frontendUrl->getUrl('vendorsmembership');
    }
    
    /**
     * Notify expiry date
     * 
     * @return bool
     */
    public function notifyExpiryDate(){
        if (!$this->getVendor()->getExpiryDate()) {
            return false;
        }
        $expiryDate = strtotime($this->getVendor()->getExpiryDate());
        $today = time();
        
        $differentTime = ($expiryDate - $today)  / (60*60*24); /*Days*/
        return $differentTime <= 7;
    }
    
    /**
     * Get payment history URL
     * 
     * @return string
     */
    public function getPaymentHistoryUrl(){
        return $this->getUrl("membership/payment/history");
    }
}
