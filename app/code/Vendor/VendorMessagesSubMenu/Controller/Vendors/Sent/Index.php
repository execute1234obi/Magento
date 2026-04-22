<?php

namespace Vendor\VendorMessagesSubMenu\Controller\Vendors\Sent;

class Index extends \Vnecoms\VendorsMessage\Controller\Vendors\Sent\Index
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->getRequest()->setParam('owner_id', $this->_session->getCustomerId());
        $this->_initAction();
        $this->setActiveMenu('Vnecoms_VendorsMessage::messages');
        $this->_addBreadcrumb(__('Message'), __('Message'));
        $this->_addBreadcrumb(__('Sent'), __('Sent'));

        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->set(__('Message'));

        $this->_view->renderLayout();
    }
}
