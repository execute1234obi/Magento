<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern;

class Edit extends Spam
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('pattern_id');
        $model = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Pattern');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This pattern no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }


        $this->_coreRegistry->register('current_pattern', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Warning patterns'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Pattern') : __('New Pattern')
        );

        $breadcrumb = $id ? __('Edit Pattern') : __('New Pattern');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}