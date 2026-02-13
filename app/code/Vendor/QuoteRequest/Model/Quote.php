<?php
namespace Vendor\QuoteRequest\Model;

class Quote extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Vendor\QuoteRequest\Model\ResourceModel\Quote::class);
    }
}
