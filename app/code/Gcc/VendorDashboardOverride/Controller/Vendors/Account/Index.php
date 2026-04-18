<?php

namespace Gcc\VendorDashboardOverride\Controller\Vendors\Account;

use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_Vendors::account';

    /**
     * Render the vendor account page with a matching title/breadcrumb.
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor Account'));
        $this->_addBreadcrumb(__('Vendor Account'), __('Vendor Account'));

        $vendor = $this->_session->getVendor();
        $this->_coreRegistry->register('current_vendor', $vendor);
        $this->_view->renderLayout();
    }
}
