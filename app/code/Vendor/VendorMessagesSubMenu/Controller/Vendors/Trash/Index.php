<?php

namespace Vendor\VendorMessagesSubMenu\Controller\Vendors\Trash;

class Index extends \Vnecoms\VendorsMessage\Controller\Vendors\Trash\Index
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
        $this->_addBreadcrumb(__('Trash'), __('Trash'));

        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__('Message'));
        $title->prepend(__('Trash'));

        $this->_view->renderLayout();
    }
}
