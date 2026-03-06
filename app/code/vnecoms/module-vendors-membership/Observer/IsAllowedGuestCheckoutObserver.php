<?php

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class IsAllowedGuestCheckoutObserver implements ObserverInterface
{
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Check is allowed guest checkout if quote contain credit product(s).
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        $result = $observer->getEvent()->getResult();

        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if (($product = $item->getProduct())
                && $product->getTypeId() == \Vnecoms\VendorsMembership\Model\Product\Type\Membership::TYPE_CODE
            ) {
                $result->setIsAllowed(false);
                break;
            }
        }

        return $this;
    }
}
