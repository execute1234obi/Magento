<?php
namespace VendorName\VendorDashboardLink\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
//use Vnecoms\Marketplace\Model\Vendor; // Assuming this is the correct path for VnEcoms Vendor model
//use Vnecoms\Marketplace\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Vnecoms\Vendors\Model\Vendor; // Assuming this is the correct path for VnEcoms Vendor model
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;

class DashboardLink extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var Vendor|null
     */
    protected $vendor = null;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param VendorCollectionFactory $vendorCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        VendorCollectionFactory $vendorCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        parent::__construct($context, $data);
    }
      /**
     * Get customer session.
     *
     * @return CustomerSession
     */
    public function getCustomerSession() // ADD THIS NEW PUBLIC METHOD
    {
        return $this->customerSession;
    }

    /**
     * Check if the current customer is a vendor.
     *
     * @return bool
     */
    public function isCustomerVendor()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }

        if ($this->vendor === null) {
            $customerId = $this->customerSession->getCustomerId();
            $collection = $this->vendorCollectionFactory->create()
                ->addFilter('customer_id', $customerId) 
                 ->addFieldToFilter('status', ['in' => [Vendor::STATUS_APPROVED, Vendor::STATUS_PENDING]]);
            $this->vendor = $collection->getFirstItem();
        }

        return $this->vendor && $this->vendor->getId();
    }

    /**
     * Get the URL for the vendor dashboard.
     *
     * @return string
     */
    public function getVendorDashboardUrl()
    {
        // This is the default VnEcoms vendor dashboard URL. Confirm in your VnEcoms config/routes.xml
        return $this->getUrl('vendors/dashboard');
    }

    /**
     * Get the URL for the vendor registration/redirection page.
     *
     * @return string
     */
    public function getBecomeVendorUrl()
    {
        // This is the default VnEcoms vendor registration URL. Confirm in your VnEcoms config/routes.xml
        return $this->getUrl('marketplace/seller/register');
    }

    /**
     * Get the label for the link.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLinkText()
    {
        return $this->isCustomerVendor() ? __('Vendor Dashboard') : __('Become a Vendor');
    }

    /**
     * Get the target URL for the link.
     *
     * @return string
     */
    public function getLinkUrl()
    {
        return $this->isCustomerVendor() ? $this->getVendorDashboardUrl() : $this->getBecomeVendorUrl();
    }
}
