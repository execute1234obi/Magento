<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning;
use Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning\Warning;

class View extends Warning
{

    /**
     * @return void
     */
    public function execute()
    {
        $warning = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Warning');
        $warning->load($this->getRequest()->getParam('id'));

        if(!$warning->getId()){
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('vendors/message_warning');
        }

        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($warning->getMessageId());

        if(!$message->getId()){
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('vendors/message_warning');
        }

        $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
        $messageDetail->load($warning->getDetailMessageId());

        if(!$messageDetail->getId()){
            $this->messageManager->addError(__("The message detail is not available !"));
            return $this->_redirect('vendors/message_warning');
        }


        $this->_coreRegistry->register('detail_message', $messageDetail);
        $this->_coreRegistry->register('message', $message);
        $this->_coreRegistry->register('warning', $warning);

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
