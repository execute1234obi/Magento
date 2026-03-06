<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class MassDelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('selected');
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        
        foreach ($ids as $id) {
            $message->load($id)->trash();
        }
        
        $this->messageManager->addSuccess(__("%1 message(s) has been deleted.", sizeof($ids)));
        return $this->_redirect('customer/message');
    }
}
