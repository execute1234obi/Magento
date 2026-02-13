<?php
namespace Vendor\VendorMessagesSubMenu\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\VendorsMessage\Model\MessageFactory;

class AddVendorInboxCount implements ObserverInterface
{
    protected $vendorSession;
    protected $messageFactory;

    public function __construct(
        VendorSession $vendorSession,
        MessageFactory $messageFactory
    ) {
        $this->vendorSession = $vendorSession;
        $this->messageFactory = $messageFactory;
    }

    public function execute(Observer $observer)
    {
        $menu = $observer->getMenu();
        $menuItem = $menu->get('Vendor_VendorMessagesSubMenu::inbox');

        if ($menuItem) {
            $vendor = $this->vendorSession->getVendor();
            if ($vendor && $vendor->getId()) {
                // Get unread message count for this vendor
                $count = $this->messageFactory->create()->getCollection()
                    ->addFieldToFilter('receiver_id', $vendor->getId())
                    ->addFieldToFilter('is_read', 0)
                    ->getSize();

                // Update menu title
                $menuItem->setTitle(__('Inbox (%1)', $count));
            }
        }
    }
}
