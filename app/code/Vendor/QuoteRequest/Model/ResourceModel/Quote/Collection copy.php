<?php
namespace Vendor\QuoteRequest\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\QuoteRequest\Model\Quote;
use Vendor\QuoteRequest\Model\ResourceModel\Quote as QuoteResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Quote::class, QuoteResource::class);
    }
}