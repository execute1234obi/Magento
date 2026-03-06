<?php

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\Vendors\Model\Vendor;

/**
 * AdminNotification observer.
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SetRenderer implements ObserverInterface
{
    /**
     * Add the notification if there are any vendor awaiting for approval. 
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getForm();
        $layout = $observer->getLayout();

        $durationField = $form->getElement('vendor_membership_duration');
        if ($durationField) {
            $durationField->setRenderer(
                $layout->createBlock('Vnecoms\VendorsMembership\Block\Adminhtml\Product\Edit\Renderer\Duration')
            );
        }
    }
}
