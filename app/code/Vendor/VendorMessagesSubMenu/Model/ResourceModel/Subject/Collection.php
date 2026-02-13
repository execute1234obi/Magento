<?php
namespace Vendor\VendorMessages\Model\ResourceModel\Subject;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Vendor\VendorMessages\Model\Subject::class,
            \Vendor\VendorMessages\Model\ResourceModel\Subject::class
        );
    }
}
