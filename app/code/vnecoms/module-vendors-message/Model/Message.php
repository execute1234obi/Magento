<?php
namespace Vnecoms\VendorsMessage\Model;

class Message extends \Magento\Framework\Model\AbstractModel
{

    const ENTITY = 'vendor_message';
    
    const STATUS_DRAFT      = 0;
    const STATUS_UNDREAD    = 1;
    const STATUS_READ       = 2;
    const STATUS_SENT       = 3;
    
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_message';
    
    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_message';

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail\Collection
     */
    protected $_messageDetailCollection;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail
     */
    protected $_firstMessageDetail;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail
     */
    protected $_lastMessageDetail;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\Message
     */
    protected $_relationMessage;
    
    /**
     * Initialize customer model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Message');
    }
    
    /**
     * Get Message Detail Collection
     *
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail\Collection
     */
    public function getMessageDetailCollection()
    {
        if (!$this->_messageDetailCollection) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_messageDetailCollection = $om->create('Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail\Collection');
            $this->_messageDetailCollection->addFieldToFilter('message_id', $this->getId());
            $this->_messageDetailCollection->setOrder('detail_id', 'ASC');
        }
        
        return $this->_messageDetailCollection;
    }
    
    /**
     * Get First message Detail
     *
     * @return \Vnecoms\VendorsMessage\Model\Message\Detail
     */
    public function getFirstMessageDetail()
    {
        if (!$this->_firstMessageDetail) {
            if ($this->_messageDetailCollection) {
                $this->_firstMessageDetail = $this->_messageDetailCollection->getFirstItem();
            }
            
            $this->_firstMessageDetail = $this->getResource()->getFirstMessageDetail($this);
        }
        
        return $this->_firstMessageDetail;
    }
    
    /**
     * Get Last message Detail
     *
     * @return \Vnecoms\VendorsMessage\Model\Message\Detail
     */
    public function getLastMessageDetail()
    {
        if (!$this->_lastMessageDetail) {
            if ($this->_messageDetailCollection) {
                $this->_lastMessageDetail = $this->_messageDetailCollection->getLastItem();
            }
        
            $this->_lastMessageDetail = $this->getResource()->getLastMessageDetail($this);
        }
        
        return $this->_lastMessageDetail;
    }
    
    /**
     * The relation message is the message that have same identifier with the current message.
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getRelationMessage()
    {
        if (!$this->_relationMessage) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $om->create('Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection');
            $collection->addFieldToFilter('identifier', $this->getIdentifier())
                ->addFieldToFilter('message_id', ['neq' => $this->getId()]);
            if ($collection->count()) {
                $this->_relationMessage = $collection->getFirstItem();
            } else {
                /*Create Relation Message*/
                $this->_relationMessage = $om->create('Vnecoms\VendorsMessage\Model\Message');
                $firstMessageDetail = $this->getFirstMessageDetail();
                $relationOwnerId = $this->getOwnerId() != $firstMessageDetail->getSenderId()?$firstMessageDetail->getSenderId():$firstMessageDetail->getReceiverId();
                $this->_relationMessage->setData([
                    'identifier' => $this->getIdentifier(),
                    'owner_id' => $relationOwnerId,
                    'status' => Message::STATUS_UNDREAD,
                    'is_inbox' => 1,
                    'is_outbox' => 0,
                    'is_deleted' => 0,
                ]);
            }
        }
        return $this->_relationMessage;
    }
    
    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        if ($this->getStatus() == self::STATUS_UNDREAD) {
            $this->setStatus(self::STATUS_READ)->save();
        }
        $this->getResource()->markAsRead($this);
    }
    
    /**
     * Mark the message as unread.
     */
    public function markAsUnread()
    {
        if ($this->getStatus() == self::STATUS_READ) {
            $this->setStatus(self::STATUS_UNDREAD)->save();
        }
    }
    
    /**
     * Move the message to trash box
     */
    public function trash()
    {
        $this->setIsDeleted(1)
            /* ->setIsInbox(0)
            ->setIsOutbox(0) */
            ->save();
    }
    
    /**
     * Undelete action
     */
    public function unTrash()
    {
        $isInbox = $isOutbox = 0;
        
        foreach ($this->getMessageDetailCollection() as $detail) {
            if ($detail->getReceiverId() == $this->getOwnerId()) {
                $isInbox = 1;
            }
            if ($detail->getSenderId() == $this->getOwnerId()) {
                $isOutbox = 1;
            }
            if ($isInbox && $isOutbox) {
                break;
            }
        }
        
        $this->setIsDeleted(0)
            ->setIsInbox($isInbox)
            ->setIsOutbox($isOutbox)
            ->save();
    }
}
