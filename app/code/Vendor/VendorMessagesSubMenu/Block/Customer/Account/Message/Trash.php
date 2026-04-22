<?php
namespace Vendor\VendorMessagesSubMenu\Block\Customer\Account\Message;

/**
 * Hum core Trash ki bajaye apni Custom Inbox ko extend karenge
 * taaki getDisplayName() function yahan bhi available ho jaye.
 */
class Trash extends \Vendor\VendorMessagesSubMenu\Block\Customer\Account\Message\Inbox
{
    /**
     * Get Message Collection (Trash logic: is_deleted = 1)
     */
    public function getMessageCollection()
    {
        if (!$this->_messageCollection) {
            $collection = $this->_messageFactory->create()->getCollection();
            $collection->addFieldToFilter('owner_id', $this->_customerSession->getCustomerId())
                ->addFieldToFilter('is_deleted', 1) // Trash ke liye filter
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

    /**
     * Delete Forever URL
     */
    public function getDeleteMessagesURL()
    {
        return $this->getUrl('customer/message/deleteForever');
    }
}