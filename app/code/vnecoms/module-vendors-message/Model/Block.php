<?php

namespace Vnecoms\VendorsMessage\Model;

use Magento\Framework\Model\AbstractModel;

class Block extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Block');
    }
}