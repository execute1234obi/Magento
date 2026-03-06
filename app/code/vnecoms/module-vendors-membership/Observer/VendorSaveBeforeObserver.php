<?php

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class VendorSaveBeforeObserver implements ObserverInterface
{

    /**
     * @var \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Vnecoms\VendorsMembership\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->helper = $helper;
        $this->date = $date;
    }

    /**
     * Set default expiry date for new vendor.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendor = $observer->getVendor();
        if(!$vendor->getId()) {
            $currentTime = $this->date->date();
            $duration = $this->helper->getDefaultExpiryDay();
            $expiryTime = strtotime($currentTime."+$duration days");
            
            $vendor->setExpiryDate($expiryTime);
        }
    }
}
