<?php

namespace Vnecoms\VendorsMessage\Controller\Vendors\View;

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
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($this->getRequest()->getParam('id'));
        
        if (!$message->getId() || $message->getOwnerId() != $this->_session->getCustomerId()) {
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('message');
        }
        
        $this->_coreRegistry->register('current_message', $message);
        $this->_coreRegistry->register('message', $message);
        
        $this->_initAction();
        $this->setActiveMenu('Vnecoms_VendorsMessage::messages');
        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__("Messages"));
        $title->prepend(__("View Message"));
        $this->_addBreadcrumb(__("Messages"), __("Messages"))
            ->_addBreadcrumb(__("View"), __("View"));
        $this->_view->renderLayout();
        
        /*Mark the message as read after render the message*/
        $message->markAsRead();
    }
}
