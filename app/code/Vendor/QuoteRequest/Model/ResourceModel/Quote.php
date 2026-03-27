<?php
namespace Vendor\QuoteRequest\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    protected function _construct()
    {
        // Table name aur Primary key ka column name
        $this->_init('vendor_quote', 'quote_id');
    }
}