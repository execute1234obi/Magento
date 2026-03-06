<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Delete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
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
        $id = $this->getRequest()->getParam('id');
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($id);
        if (!$id || !$message->getId() || $message->getOwnerId() != $this->_customerSession->getCustomerId()) {
            $this->messageManager->addError(__("The message is not available."));
            return $this->_redirect('customer/message');
        }
        
        $message->trash();
        
        $this->messageManager->addSuccess(__("The message has been deleted!"));
        return $this->_redirect('customer/message');
    }
}
