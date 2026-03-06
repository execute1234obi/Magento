<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning;

class Block extends Warning
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // init model and delete
                $warning = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Warning');
                $warning->load($id);

                if(!$warning->getId()){
                    $this->messageManager->addError(__("The warning message is not available !"));
                    return $this->_redirect('vendors/message_warning');
                }

                $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
                $messageDetail->load($warning->getDetailMessageId());

                if(!$messageDetail->getId()){
                    $this->messageManager->addError(__("The message detail is not available !"));
                    return $this->_redirect('vendors/message_warning');
                }

                // save sender to block list user
                $senderId = $messageDetail->getSenderId();
                $block = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Block');
                $sender = $block->load($senderId,"owner_id");
                if(!$sender->getId()){
                    $block->setData("owner_id",$senderId)->save();
                }
                
                // delete warning message
                $warning->delete();
                // display success message
                $this->messageManager->addSuccess(__('You blocked the sender.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/view', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a warning message to block.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
