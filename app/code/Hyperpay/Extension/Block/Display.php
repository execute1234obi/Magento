<?php
namespace Hyperpay\Extension\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Hyperpay\Extension\Model\Adapter;
use Hyperpay\Extension\Helper\Data;

class Display extends Template
{
    protected $storeLocale;
    protected $registry;
    protected $helper;
    protected $adapter;

    public function __construct(
        Template\Context $context,
        Resolver $storeLocale,
        Registry $registry,
        Adapter $adapter,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper      = $helper;
        $this->storeLocale = $storeLocale;
        $this->adapter     = $adapter;
        $this->registry    = $registry;
    }

    /**
     * Retrieve payment brand
     */
    public function getPaymentBrand()
    {
        return $this->helper->getBrand();
    }

    /**
     * Retrieve payment form URL
     */
    public function getFormUrl()
    {
        return $this->registry->registry('formurl') ?: '';
    }

    /**
     * Retrieve shopper status URL
     */
    public function getShopperUrl()
    {
        return $this->registry->registry('status') ?: '';
    }

    /**
     * Language detection from locale
     */
    public function getLang()
    {
        $locale = $this->storeLocale->getLocale();   // Safe in all versions
        return substr($locale, 0, 2);                // Return 'en', 'ar', etc.
    }

    /**
     * Retrieve payment style
     */
    public function getStyle()
    {
        return $this->adapter->getStyle() ?: '';
    }

    /**
     * Retrieve payment CSS
     */
    public function getCss()
    {
        return $this->adapter->getCss() ?: '';
    }
}
