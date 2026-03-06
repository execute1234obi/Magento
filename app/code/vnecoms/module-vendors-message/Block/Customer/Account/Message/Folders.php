<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account\Message;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Shopping cart item render block for configurable products.
 */
class Folders extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\MessageFactory
     */
    protected $_messageFactory;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    protected $_unreadMessageCollection;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_messageFactory = $messageFactory;
        
        parent::__construct($context, $data);
    }
   
   /**
    * Get Unread Message Collection
    *
    * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
    */
    public function getUnreadMessageCollection()
    {
        if (!$this->_unreadMessageCollection) {
            $this->_unreadMessageCollection = $this->_messageFactory->create()->getCollection();
            $this->_unreadMessageCollection->addFieldToFilter('owner_id', $this->_customerSession->getCustomerId())
            ->addFieldToFilter('status', \Vnecoms\VendorsMessage\Model\Message::STATUS_UNDREAD)
            ->addFieldToFilter('is_inbox', 1)
            ->addFieldToFilter('is_deleted', 0)
            ->setOrder('message_id', 'DESC')
            ->setPageSize(5);
        }
    
        return $this->_unreadMessageCollection;
    }
    
    
    /**
     * Get Unread Message Count
     *
     * @return int
     */
    public function getUnreadMessageCount()
    {
        return $this->getUnreadMessageCollection()->getSize();
    }
   
   /**
    * Get Inbox URL
    *
    * @return string
    */
    public function getInboxURL()
    {
        return $this->getUrl("customer/message");
    }
    
    /**
     * Get Out Box URL
     *
     * @return string
     */
    public function getOutboxURL()
    {
        return $this->getUrl("customer/message/sent");
    }
    
    /**
     * Get Draft URL
     *
     * @return string
     */
    public function getDraftURL()
    {
        return $this->getUrl("customer/message/draft");
    }
    
    /**
     * Get Trash URL
     *
     * @return string
     */
    public function getTrashURL()
    {
        return $this->getUrl("customer/message/trash");
    }
    
    /**
     * Is Active Box
     *
     * @param string $box
     * @return boolean
     */
    public function isActiveBox($box)
    {
        return $box == $this->getActiveBox();
    }
}
