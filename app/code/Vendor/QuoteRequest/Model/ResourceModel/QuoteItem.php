<?php
namespace Vendor\QuoteRequest\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuoteItem extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vendor_quote_item', 'item_id'); // table name and primary key
    }
}
