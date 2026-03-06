<?php
namespace Vnecoms\VendorsMessage\Block\Profile;

class Message extends \Vnecoms\VendorsMessage\Block\Message
{
    /**
     * return nothing if the vendor object is not exist
     *
     * @see \Magento\Framework\View\Element\Template::_toHtml()
     */
    protected function _toHtml()
    {
        if (!$this->getVendor()) {
            return '';
        }
        return parent::_toHtml();
    }
}
