<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning\Warning;

class Index extends Warning
{
    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction()->_addBreadcrumb(__('Message'), __('Warning Message'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Message - Warning'));
        $this->_view->renderLayout();
    }
}