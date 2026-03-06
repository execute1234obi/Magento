<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern;

class NewAction extends Spam
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}