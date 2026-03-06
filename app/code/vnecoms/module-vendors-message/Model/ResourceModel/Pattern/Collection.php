<?php

namespace Vnecoms\VendorsMessage\Model\ResourceModel\Pattern;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'pattern_id';


    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Vnecoms\VendorsMessage\Model\Pattern',
            'Vnecoms\VendorsMessage\Model\ResourceModel\Pattern'
        );
    }
}