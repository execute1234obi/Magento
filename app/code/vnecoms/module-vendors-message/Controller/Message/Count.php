<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Count extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Count constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_messageFactory = $messageFactory;
        $this->httpContext = $httpContext;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $response =[
            "count" =>  $this->getUnreadMessageCount()
        ];

        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }


    /**
     * Get Unread Message Collection
     *
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    protected function getUnreadMessageCollection()
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
}
