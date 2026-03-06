<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $messageId = $this->getRequest()->getParam('id');
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($messageId);
        
        if (!$messageId || !$message->getId() || $message->getOwnerId() != $this->_customerSession->getCustomerId()) {
            $this->messageManager->addError('The message is not available.');
            return $this->_redirect('customer/message');
        }
        
        $this->_coreRegistry->register('message', $message);
        $this->_coreRegistry->register('current_message', $message);
        
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $title = $resultPage->getConfig()->getTitle();
        $title->set('My Messages');
        $title->prepend($message->getFirstMessageDetail()->getSubject());
        $this->_view->loadLayout();
        if($box = $this->getRequest()->getParam('box')){
            $this->_view->getLayout()->getBlock('customer.message.folders')->setActiveBox($box);
        }
        $this->_view->renderLayout();
        /*Mark the message as read after render the message*/
        $message->markAsRead();
    }
}
