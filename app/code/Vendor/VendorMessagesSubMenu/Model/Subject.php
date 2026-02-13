<?php
namespace Vendor\VendorMessages\Model;

use Magento\Framework\Model\AbstractModel;

class Subject extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Vendor\VendorMessages\Model\ResourceModel\Subject::class);
    }
}
