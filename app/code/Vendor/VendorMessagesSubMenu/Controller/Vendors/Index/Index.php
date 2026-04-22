<?php

namespace Vendor\VendorMessagesSubMenu\Controller\Vendors\Index;

class Index extends \Vnecoms\VendorsMessage\Controller\Vendors\Index\Index
{
    /**
     * Execute request
     *
     * @return void
     */
    public function execute()
    {
        $this->getRequest()->setParam('owner_id', $this->_session->getCustomerId());
        $this->_initAction();
        $this->setActiveMenu('Vnecoms_VendorsMessage::messages');
        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__('Message'));
        $this->_addBreadcrumb(__('Message'), __('Message'))
            ->_addBreadcrumb(__('Inbox'), __('Inbox'));
        $this->_view->renderLayout();
    }
}
