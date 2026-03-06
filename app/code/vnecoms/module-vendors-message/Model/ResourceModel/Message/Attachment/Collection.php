<?php
/**
 * Copyright (c) 2017 Vnecoms Co ltd. All rights reserved.
 */

namespace Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'attachment_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Vnecoms\VendorsMessage\Model\Message\Attachment',
            'Vnecoms\VendorsMessage\Model\ResourceModel\Message\Attachment'
        );
    }
}
