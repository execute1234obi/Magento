<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Controller\Adminhtml\Membership\Product;

use Vnecoms\VendorsMembership\Controller\Adminhtml\Action;

class Index extends Action
{
    /**
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Seller Membership Products'), __('Seller Membership Products'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Seller Membership Products'));
        $this->_view->renderLayout();
    }
}
