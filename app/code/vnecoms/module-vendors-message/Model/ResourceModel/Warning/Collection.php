<?php

namespace Vnecoms\VendorsMessage\Model\ResourceModel\Warning;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'warning_id';


    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Vnecoms\VendorsMessage\Model\Warning',
            'Vnecoms\VendorsMessage\Model\ResourceModel\Warning'
        );
    }
}