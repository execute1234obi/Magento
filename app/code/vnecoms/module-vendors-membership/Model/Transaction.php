<?php

namespace Vnecoms\VendorsMembership\Model;

class Transaction extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\VendorsMembership\Model\ResourceModel\Transaction');
    }
}
