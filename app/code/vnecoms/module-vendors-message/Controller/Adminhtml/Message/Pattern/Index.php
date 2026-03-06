<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern\Spam;

class Index extends Spam
{
    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction()->_addBreadcrumb(__('Message'), __('Warning Patterns'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Message - Warning Patterns'));
        $this->_view->renderLayout();
    }
}