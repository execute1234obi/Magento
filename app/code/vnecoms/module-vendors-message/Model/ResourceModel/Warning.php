<?php

namespace Vnecoms\VendorsMessage\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Warning extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_message_warning', 'warning_id');
    }
}