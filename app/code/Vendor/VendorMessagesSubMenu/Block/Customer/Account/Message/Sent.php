<?php
namespace Vendor\VendorMessagesSubMenu\Block\Customer\Account\Message;

/**
 * Hum apni hi custom Inbox class ko extend karenge 
 * taaki getDisplayName() automatically mil jaye
 */
class Sent extends \Vendor\VendorMessagesSubMenu\Block\Customer\Account\Message\Inbox
{
    /**
     * Core Sent class ka logic yahan copy karein
     */
    public function getMessageCollection()
    {
        if (!$this->_messageCollection) {
            $collection = $this->_messageFactory->create()->getCollection();
            $collection->addFieldToFilter('owner_id', $this->_customerSession->getCustomerId())
                ->addFieldToFilter('is_outbox', 1) // Ye outbox (Sent) ke liye zaroori hai
                ->addFieldToFilter('is_deleted', 0)
                ->setOrder('message_id', 'DESC');
            
            $collection->getSelect()->joinLeft(
                ['detail' => $collection->getTable('ves_vendor_message_detail')], 
                'main_table.message_id = detail.message_id', 
                ['msg_count' => 'count(detail_id)']
            );
            $collection->getSelect()->group('detail.message_id');
            $this->_messageCollection = $collection;
        }
    
        return $this->_messageCollection;
    }
}