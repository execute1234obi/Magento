<?php

namespace Vnecoms\VendorsMessage\Model\ResourceModel\Block;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'block_id';


    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Vnecoms\VendorsMessage\Model\Block',
            'Vnecoms\VendorsMessage\Model\ResourceModel\Block'
        );
    }
}