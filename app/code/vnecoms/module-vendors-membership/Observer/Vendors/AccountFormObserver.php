<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Observer\Vendors;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\Vendors\Model\Vendor;

/**
 * AdminNotification observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AccountFormObserver implements ObserverInterface
{
    /**
     * Add the notification if there are any vendor awaiting for approval.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getForm();
        $tab = $observer->getTab();
        $element = $form->getElement('expiry_date');
        
        if(!$element) return;
        $renderer = $tab->getLayout()->createBlock('Vnecoms\VendorsMembership\Block\Vendors\Account\Edit\Form\Renderer\ExpiryDate');
        $element->setRenderer($renderer);
    }


}
