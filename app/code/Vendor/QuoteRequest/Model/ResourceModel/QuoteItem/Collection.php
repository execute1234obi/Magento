<?php
namespace Vendor\QuoteRequest\Model\ResourceModel\QuoteItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Vendor\QuoteRequest\Model\QuoteItem::class,
            \Vendor\QuoteRequest\Model\ResourceModel\QuoteItem::class
        );
    }
}