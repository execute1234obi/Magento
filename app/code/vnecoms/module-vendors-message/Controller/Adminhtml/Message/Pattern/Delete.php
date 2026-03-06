<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern;

class Delete extends Spam
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
        $id = $this->getRequest()->getParam('pattern_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Pattern');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the pattern.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['pattern_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a pattern to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
