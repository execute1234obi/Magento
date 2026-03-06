<?php
/**
 * Copyright (c) 2017 Vnecoms Co ltd. All rights reserved.
 */

namespace Vnecoms\VendorsMessage\Model\ResourceModel\Message;

class Attachment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_message_attachment', 'attachment_id');
    }
}
