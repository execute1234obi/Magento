<?php
namespace Vendor\CustomConfig\Model;

use Magento\Framework\Model\AbstractModel;

class Vendor extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Vendor\CustomConfig\Model\ResourceModel\Vendor::class);
    }
}