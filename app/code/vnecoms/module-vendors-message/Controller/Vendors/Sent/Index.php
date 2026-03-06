<?php

namespace Vnecoms\VendorsMessage\Controller\Vendors\Sent;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_VendorsMessage::messages';
    /**
     * @return void
     */
    public function execute()
    {
        $this->getRequest()->setParam('owner_id', $this->_session->getCustomerId());
        $this->_initAction();
        $this->setActiveMenu('Vnecoms_VendorsMessage::messages');
        $this->_addBreadcrumb(__("Messages"), __("Messages"))->_addBreadcrumb(__("Sent Messages"), __("Sent Messages"));
        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__("Messages"));
        $title->prepend(__("Sent"));
        $this->_view->renderLayout();
    }
}
