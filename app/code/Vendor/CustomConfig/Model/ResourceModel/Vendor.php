<?php
namespace Vendor\CustomConfig\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Vendor extends AbstractDb
{
    protected function _construct()
    {
        // The first argument is the table name, the second is the primary key.
        $this->_init('ves_vendor_entity', 'entity_id');
    }
}