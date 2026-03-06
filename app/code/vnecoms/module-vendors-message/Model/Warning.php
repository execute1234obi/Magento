<?php

namespace Vnecoms\VendorsMessage\Model;

use Magento\Framework\Model\AbstractModel;

class Warning extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Warning');
    }
}