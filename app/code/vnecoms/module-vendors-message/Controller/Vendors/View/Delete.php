<?php

namespace Vnecoms\VendorsMessage\Controller\Vendors\View;

class Delete extends \Vnecoms\Vendors\Controller\Vendors\Action
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
        
        $message->trash();
        
        $this->messageManager->addSuccess(__('The message has been deleted.'));
        return $this->_redirect('message');
    }
}
