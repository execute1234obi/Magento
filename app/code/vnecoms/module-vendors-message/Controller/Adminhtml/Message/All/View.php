<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\All;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\All\All;

class View extends All
{

    /**
     * @return void
     */
    public function execute()
    {

        $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
        $messageDetail->load($this->getRequest()->getParam('id'));

        if(!$messageDetail->getId()){
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('vendors/message');
        }


        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($messageDetail->getMessageId());

        if(!$message->getId()){
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('vendors/message');
        }
        
        $this->_coreRegistry->register('message', $message);

        $this->_initAction();

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
