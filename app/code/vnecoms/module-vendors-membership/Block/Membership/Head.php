<?php

namespace Vnecoms\VendorsMembership\Block\Membership;

class Head extends \Magento\Framework\View\Element\Template
{
    public function getCssUrl()
    {
        return $this->getUrl('vendorsmembership/index/css');
    }
}
