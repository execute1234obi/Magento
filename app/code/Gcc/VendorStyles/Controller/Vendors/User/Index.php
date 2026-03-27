<?php

namespace Gcc\VendorStyles\Controller\Vendors\User;

class Index extends \Vnecoms\VendorsSubAccount\Controller\Vendors\User\Index
{
    /**
     * Render the sub account users page with GCC-specific labels.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->getRequest()->setParam('vendor_id', $this->vendorSession->getVendor()->getId());

        $resultPage = $this->resultPageFactory->create();

        $this->setActiveMenu('Vnecoms_VendorsQuotation::subaccount_user');
        $this->_addBreadcrumb(__('Sub Accounts'), __('Sub Accounts'));
        $this->_addBreadcrumb(__('Manage User'), __('Manage User'));
        $resultPage->getConfig()->getTitle()->prepend(__('Sub Accounts'));

        return $resultPage;
    }
}
