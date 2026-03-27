<?php
namespace Vendor\QuoteRequest\Model\ResourceModel;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        // Aapka table name 'vendor_quote_item' aur primary key 'item_id'
        $this->_init('vendor_quote_item', 'item_id');
    }
}