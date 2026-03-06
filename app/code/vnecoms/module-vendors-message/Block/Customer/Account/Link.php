<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Shopping cart item render block for configurable products.
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
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
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_messageFactory = $messageFactory;
        
        parent::__construct($context, $defaultPath, $data);
    }
    
    /**
     * Get link label
     * @return string
     */
    public function getLabel()
    {
        $count = $this->getUnreadMessageCount();
        $labelWithCount = __(
            "%1 Message %2",
            '<i class="message-icon-envelope"></i>',
            '<span class="vnecoms-unread-message-count">'.$count."</span>"
        );
       
        $labelWithoutCount = __(
            "%1 Message",
            '<i class="message-icon-envelope"></i>'
        );
        return $count?$labelWithCount:$labelWithoutCount;
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
    * Disable escape html for this block.
    */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
    * Get current mca
    *
    * @return string
    */
    private function getMca()
    {
        $routeParts = [
           'module' => $this->_request->getModuleName(),
           'controller' => $this->_request->getControllerName(),
           'action' => $this->_request->getActionName(),
        ];
   
        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }
   
   /**
    * (non-PHPdoc)
    * @see \Magento\Framework\View\Element\Html\Link\Current::isCurrent()
    */
    public function isCurrent()
    {
        return $this->getCurrent() ||
        $this->getUrl($this->getMca()) == $this->getUrl('customer/message') ||
        $this->getUrl($this->getMca()) == $this->getUrl('customer/message/sent') ||
        $this->getUrl($this->getMca()) == $this->getUrl('customer/message/trash') ||
        $this->getUrl($this->getMca()) == $this->getUrl('customer/message/view');
    }
   
    public function toHtml()
    {
        if ($this->isCurrent()) {
            return '';
        }
        return parent::toHtml();
    }
}
