<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account\Message;

/**
 * Shopping cart item render block for configurable products.
 */
class Inbox extends \Magento\Framework\View\Element\Template
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
    protected $_messageCollection;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
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
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getMessageCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vendorsmessage.messages.pager'
            )->setCollection(
                $this->getMessageCollection()
            );
            $this->setChild('pager', $pager);
            $this->getMessageCollection()->load();
        }
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
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
            ->addFieldToFilter('is_inbox', 1)
            ->addFieldToFilter('is_deleted', 0)
            ->setOrder('message_id', 'DESC');
            $collection->getSelect()->joinLeft(['detail'=>$collection->getTable('ves_vendor_message_detail')], 'main_table.message_id = detail.message_id', ['msg_count' => 'count(detail_id)']);
            $collection->getSelect()->group('detail.message_id');
            $this->_messageCollection = $collection;
        }
    
        return $this->_messageCollection;
    }
    
    /**
     * Format message time
     *
     * @param string $dateTime
     */
    public function getMessageTime($dateTime)
    {
        //added by vru
        if (!$dateTime) {
            return '';
        }  
        //end vru     
        $messageTimeStamp = strtotime($dateTime);
        $timeStamp = time();
    
        $differentTime = $timeStamp - $messageTimeStamp;
        $minutes = round($differentTime / 60);
        if ($minutes == 0) {
            return __("Now");
        } elseif ($minutes < 60) {
            return __("%1 minutes", $minutes);
        }
    
        $hours = round($minutes / 60);
        
        if ($hours < 24) {
            return __("Today");
        }
    
        $days = round($hours / 24);
        if ($days == 1) {
            return __("Yesterday");
        }
        if ($days < 7) {
            return __("%1 days", $days);
        }
    
        if ($days < 365) {
            return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
        }
    
        return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
    }
    
    /**
     * Get delete messages URL
     *
     * @return string
     */
    public function getDeleteMessagesURL()
    {
        return $this->getUrl('customer/message/massDelete');
    }
    
    /**
     * Get View message URL
     *
     * @param \Vnecoms\VendorsMessage\Model\Message $message
     * @return string
     */
    public function getViewMessageUrl(
        \Vnecoms\VendorsMessage\Model\Message $message
    ) {
        $box = $this->getLayout()->getBlock('customer.message.folders')->getActiveBox();
        return $this->getUrl('customer/message/view', ['id' => $message->getId(), 'box' => $box]);
    }
		
		/**
     * Get Loggin customer id
     *
     * @return int
     */
		public function getCustomerId(){
			return $this->_customerSession->getCustomerId();
		}
}
