<?php
namespace Custom\HsCode\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class HsCode extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_hscode', 'hscode_id'); // replace with your actual table name and primary key
    }
}
