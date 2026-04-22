<?php

namespace Vendor\VendorMessagesSubMenu\Block\Customer\Account\Message;

use Vnecoms\VendorsMessage\Block\Customer\Account\Message\Inbox as CoreInbox;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;

class Inbox extends CoreInbox
{
    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * Simple runtime cache
     */
    protected $vendorCache = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        VendorCollectionFactory $vendorCollectionFactory,
        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        parent::__construct($context, $customerSession, $messageFactory, $data);
    }

    /**
     * Get vendor business name by customer ID (cached)
     */
    public function getVendorBusinessName($customerId)
    {
        if (!$customerId) {
            return null;
        }

        if (isset($this->vendorCache[$customerId])) {
            return $this->vendorCache[$customerId];
        }

        $vendor = $this->vendorCollectionFactory->create()
            ->addAttributeToSelect('b_name')
            ->addFieldToFilter('vendor_user_customer_id', $customerId)
            ->getFirstItem();

        $name = ($vendor && $vendor->getId())
            ? $vendor->getData('b_name')
            : null;

        $this->vendorCache[$customerId] = $name;

        return $name;
    }

    /**
     * FINAL display name resolver (IMPORTANT)
     */
    // public function getDisplayName($messageDetail, $currentCustomerId, $type = 'sender')
    // {
    //     if ($type === 'sender') {
    //         $id = $messageDetail->getSenderId();
    //         $default = $messageDetail->getSenderName();
    //     } else {
    //         $id = $messageDetail->getReceiverId();
    //         $default = $messageDetail->getReceiverName();
    //     }

    //     return $this->getVendorBusinessName($id) ?: $default;
    // }
    public function getDisplayName($messageDetail, $type = 'receiver')
{
    if ($type === 'receiver') {
        $id = $messageDetail->getReceiverId();
        $default = $messageDetail->getReceiverName();
    } else {
        $id = $messageDetail->getSenderId();
        $default = $messageDetail->getSenderName();
    }

    $business = $this->getVendorBusinessName($id);

    return $business ?: $default;
}
}