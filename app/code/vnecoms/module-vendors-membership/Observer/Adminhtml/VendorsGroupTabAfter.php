<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\Vendors\Model\Vendor;

/**
 * AdminNotification observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class VendorsGroupTabAfter implements ObserverInterface
{
    /**
     * Add the notification if there are any vendor awaiting for approval.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fieldset = $observer->getData('fieldset');

        $fieldset->addField(
            'rank',
            'text',
            [
                'name' => 'rank',
                'label' => __('Rank'),
                'title' => __('Rank'),
                'required' => true,
                'note' => __('Sellers from higher rank group will not be able to downgrade to lower rank group')
            ]
        );
    }


}
