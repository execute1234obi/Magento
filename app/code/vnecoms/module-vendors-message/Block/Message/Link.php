<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Message;

use Magento\Customer\Model\Context;

/**
 * Class Link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Link extends \Magento\Framework\View\Element\Template
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
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Link constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_messageFactory = $messageFactory;
        $this->httpContext = $httpContext;
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
            $this->_unreadMessageCollection->addFieldToFilter('owner_id', $this->getCustomerId())
                ->addFieldToFilter('status', \Vnecoms\VendorsMessage\Model\Message::STATUS_UNDREAD)
                ->addFieldToFilter('is_inbox', 1)
                ->addFieldToFilter('is_deleted', 0)
                ->setOrder('message_id', 'DESC')
                ->setPageSize(5);
        }

        return $this->_unreadMessageCollection;
    }

    /**
     * @return mixed|null
     */
    protected function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id') ? $this->httpContext->getValue('customer_id')
            : $this->_customerSession->getCustomerId();
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
     * @return string
     */
    public function getMessageUrl()
    {
        return $this->getUrl('customer/message');
    }

    public function toHtml()
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH) ) {
            return '';
        }
        return parent::toHtml();
    }
}
