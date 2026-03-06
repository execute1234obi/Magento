<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Controller\Vendors\Payment;

use Magento\Framework\Exception\NotFoundException;

class History extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Display customer wishlist.
     *
     * @return \Magento\Framework\View\Result\Page
     *
     * @throws NotFoundException
     */
    public function execute()
    {
        $this->getRequest()->setParam('vendor_id',$this->_session->getVendor()->getId());
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("Membership Payment History"));
        $this->_addBreadcrumb(__("Membership Payment History"), __("Membership Payment History"));
        $this->_view->renderLayout();
    }
}
