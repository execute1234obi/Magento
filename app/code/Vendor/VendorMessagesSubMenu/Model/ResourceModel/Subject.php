<?php
namespace Vendor\VendorMessages\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subject extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vendor_messages_subject', 'subject_id'); // table name and primary key
    }
}
