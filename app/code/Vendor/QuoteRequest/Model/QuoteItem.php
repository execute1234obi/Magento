<?php
namespace Vendor\QuoteRequest\Model;

use Magento\Framework\Model\AbstractModel;

class QuoteItem extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Vendor\QuoteRequest\Model\ResourceModel\QuoteItem::class);
    }
}