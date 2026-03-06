<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Block;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Block\Block;

class Index extends Block
{
    /**
     * @return void
     */
    public function execute()
    {

        $this->_initAction()->_addBreadcrumb(__('Message'), __('Block User'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Block User'));
        $this->_view->renderLayout();
    }
}