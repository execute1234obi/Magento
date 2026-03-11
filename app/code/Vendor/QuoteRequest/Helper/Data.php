<?php

namespace Vendor\QuoteRequest\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->urlBuilder = $urlBuilder;
    }

    public function getAddToQuoteUrl($productId)
    {
        return $this->urlBuilder->getUrl(
            'quoterequest/index/create',
            ['product_id' => $productId]
        );
    }
}