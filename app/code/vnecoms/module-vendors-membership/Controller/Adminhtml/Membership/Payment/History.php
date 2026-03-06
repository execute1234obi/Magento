<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Controller\Adminhtml\Membership\Payment;

use Vnecoms\VendorsMembership\Controller\Adminhtml\Action;

class History extends Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Membership Payment History'), __('Membership Payment History'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Membership Payment History'));
        $this->_view->renderLayout();
    }
}
