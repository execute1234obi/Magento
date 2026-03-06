<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\All;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\All\All;

class Index extends All
{
    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction()->_addBreadcrumb(__('Message'), __('All Messages'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('All Messages'));
        $this->_view->renderLayout();
    }
}