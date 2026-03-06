<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class DeleteForever extends \Magento\Customer\Controller\AbstractAccount
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
        $ids = $this->getRequest()->getParam('selected');
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $count = 0;
        foreach ($ids as $id) {
            $message->load($id);
            if (!$message->getId() || $message->getOwnerId() != $this->_customerSession->getCustomerId()) {
                $this->messageManager->addError(__("The message #%1 is not available.", $id));
            } else {
                $message->delete();
                $count ++;
            }
        }
        
        $this->messageManager->addSuccess(__("%1 message(s) has been deleted.", $count));
        return $this->_redirect('customer/message/trash');
    }
}
