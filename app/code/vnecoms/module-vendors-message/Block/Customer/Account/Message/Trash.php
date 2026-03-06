<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account\Message;

/**
 * Shopping cart item render block for configurable products.
 */
class Trash extends \Vnecoms\VendorsMessage\Block\Customer\Account\Message\Inbox
{
   /**
    * Get Unread Message Collection
    *
    * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
    */
    public function getMessageCollection()
    {
        if (!$this->_messageCollection) {
            $collection = $this->_messageFactory->create()->getCollection();
            $collection->addFieldToFilter('owner_id', $this->_customerSession->getCustomerId())
            ->addFieldToFilter('is_deleted', 1)
            ->setOrder('message_id', 'DESC');
            $collection->getSelect()->joinLeft(['detail'=>$collection->getTable('ves_vendor_message_detail')], 'main_table.message_id = detail.message_id', ['msg_count' => 'count(detail_id)']);
            $collection->getSelect()->group('detail.message_id');
            $this->_messageCollection = $collection;
        }
    
        return $this->_messageCollection;
    }
    
    /**
     * Get delete messages URL
     *
     * @return string
     */
    public function getDeleteMessagesURL()
    {
        return $this->getUrl('customer/message/deleteForever');
    }
}
