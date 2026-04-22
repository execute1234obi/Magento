<?php

namespace Vendor\VendorMessagesSubMenu\Block\Vendors\Messages;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vnecoms\VendorsMessage\Helper\Data as MessageHelper;
use Vnecoms\VendorsMessage\Model\MessageFactory;
use Vnecoms\VendorsMessage\Model\ResourceModel\Message\Grid\OutboxCollectionFactory;

class Sent extends \Vnecoms\VendorsMessage\Block\Vendors\Messages\View
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var OutboxCollectionFactory
     */
    private $outboxCollectionFactory;

    /**
     * @var VendorSession
     */
    private $vendorSession;

    /**
     * @var \Vnecoms\VendorsMessage\Model\Message|null
     */
    private $selectedMessage;

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Grid\OutboxCollection|null
     */
    private $threadCollection;

    public function __construct(
        Template\Context $context,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        Registry $coreRegistry,
        MessageHelper $helper,
        CustomerRepositoryInterface $customerRepository,
        MessageFactory $messageFactory,
        OutboxCollectionFactory $outboxCollectionFactory,
        VendorSession $vendorSession,
        array $data = []
    ) {
        $this->messageFactory = $messageFactory;
        $this->outboxCollectionFactory = $outboxCollectionFactory;
        $this->vendorSession = $vendorSession;
        parent::__construct($context, $url, $coreRegistry, $helper, $customerRepository, $data);
    }

    public function getTitle()
    {
        return $this->getData('block_title') ? $this->getData('block_title') : __('Sent');
    }

    public function getThreadCollection()
    {
        if ($this->threadCollection) {
            return $this->threadCollection;
        }

        $collection = $this->outboxCollectionFactory->create();
        $customerId = (int)$this->vendorSession->getCustomerId();

        if ($customerId) {
            $collection->addFieldToFilter('owner_id', $customerId);
        }

        $collection->addFieldToFilter('is_outbox', 1);
        $collection->addFieldToFilter('is_deleted', 0);
        $collection->setOrder('message_id', 'DESC');

        $this->threadCollection = $collection;

        return $this->threadCollection;
    }

    public function hasSelectedMessage()
    {
        return (bool)$this->getSelectedMessage()->getId();
    }

    public function getSelectedMessage()
    {
        if ($this->selectedMessage) {
            return $this->selectedMessage;
        }

        $messageId = (int)$this->getRequest()->getParam('id');
        if ($messageId) {
            $message = $this->loadMessageById($messageId);
            if ($message && $message->getId()) {
                $this->selectedMessage = $message;
                return $this->selectedMessage;
            }
        }

        $firstMessage = $this->getThreadCollection()->getFirstItem();
        if ($firstMessage && $firstMessage->getId()) {
            $this->selectedMessage = $this->loadMessageById((int)$firstMessage->getId());
        }

        if (!$this->selectedMessage || !$this->selectedMessage->getId()) {
            $this->selectedMessage = $this->messageFactory->create();
        }

        return $this->selectedMessage;
    }

    public function getMessage()
    {
        return $this->getSelectedMessage();
    }

    public function getThreadUrl($messageId)
    {
        return $this->getUrl('message/sent/index', ['id' => (int)$messageId]);
    }

    public function getBackUrl()
    {
        return $this->getUrl('message/sent');
    }

    public function getConversationSubject()
    {
        $message = $this->getSelectedMessage();
        if (!$message->getId()) {
            return '';
        }

        $detail = $message->getFirstMessageDetail();
        if ($detail && $detail->getSubject()) {
            return (string)$detail->getSubject();
        }

        return (string)$message->getData('subject');
    }

    public function getConversationPartnerName()
    {
        $message = $this->getSelectedMessage();
        if (!$message->getId()) {
            return '';
        }

        $detail = $message->getFirstMessageDetail();
        if (!$detail) {
            return '';
        }

        if ((int)$detail->getSenderId() === (int)$message->getOwnerId()) {
            return (string)($detail->getReceiverName() ?: $detail->getSenderName());
        }

        return (string)($detail->getSenderName() ?: $detail->getReceiverName());
    }

    public function getConversationDateLabel()
    {
        $detail = $this->getSelectedMessage()->getFirstMessageDetail();
        if (!$detail || !$detail->getCreatedAt()) {
            return '';
        }

        return $this->formatConversationDate($detail->getCreatedAt());
    }

    public function getConversationTimeLabel()
    {
        $detail = $this->getSelectedMessage()->getFirstMessageDetail();
        if (!$detail || !$detail->getCreatedAt()) {
            return '';
        }

        return $this->formatConversationTime($detail->getCreatedAt());
    }

    public function getThreadSubject($message)
    {
        return (string)$message->getSubject();
    }

    public function getThreadReceiverName($message)
    {
        return (string)($message->getReceiverName() ?: $message->getSenderName());
    }

    public function getThreadDateLabel($message)
    {
        $createdAt = $message->getCreatedAt();
        if (!$createdAt) {
            return '';
        }

        return $this->formatThreadDate($createdAt);
    }

    public function getThreadSearchText($message)
    {
        return strtolower(trim(implode(' ', array_filter([
            (string)$message->getSubject(),
            (string)$message->getReceiverName(),
            (string)$message->getCreatedAt(),
        ]))));
    }

    public function getThreadPosition()
    {
        $selectedId = $this->getSelectedMessage()->getId();
        if (!$selectedId) {
            return 0;
        }

        $index = 1;
        foreach (array_values($this->getThreadCollection()->getItems()) as $thread) {
            if ((int)$thread->getId() === (int)$selectedId) {
                return $index;
            }
            $index++;
        }

        return 1;
    }

    public function getThreadCount()
    {
        return (int)$this->getThreadCollection()->getSize();
    }

    public function getPrevThreadUrl()
    {
        $position = $this->getThreadPosition();
        if ($position <= 1) {
            return '';
        }

        $thread = $this->getThreadByPosition($position - 1);
        return $thread ? $this->getThreadUrl($thread->getId()) : '';
    }

    public function getNextThreadUrl()
    {
        $position = $this->getThreadPosition();
        $count = $this->getThreadCount();
        if (!$count || $position >= $count) {
            return '';
        }

        $thread = $this->getThreadByPosition($position + 1);
        return $thread ? $this->getThreadUrl($thread->getId()) : '';
    }

    public function getJsLayout()
    {
        $message = $this->getSelectedMessage();

        $this->jsLayout['components']['vendor-messages']['component'] = 'Vendor_VendorMessagesSubMenu/js/messages-sent';
        $this->jsLayout['components']['vendor-messages']['template'] = 'Vendor_VendorMessagesSubMenu/messages-sent';
        $this->jsLayout['components']['vendor-messages']['messages'] = $this->getMessages();
        $this->jsLayout['components']['vendor-messages']['message_id'] = $message->getId();
        $this->jsLayout['components']['vendor-messages']['loader_image'] =
            $this->getViewFileUrl('images/loader-2.gif');
        $this->jsLayout['components']['vendor-messages']['message_subject'] = $this->getConversationSubject();
        $this->jsLayout['components']['vendor-messages']['addMessageUrl'] = $this->getSendUrl();
        $this->jsLayout['components']['vendor-messages']['content_css'] = $this->getContentCss();

        return \Laminas\Json\Json::encode($this->jsLayout);
    }

    private function loadMessageById($messageId)
    {
        $message = $this->messageFactory->create()->load($messageId);
        if (!$message->getId()) {
            return null;
        }

        if ((int)$message->getOwnerId() !== (int)$this->vendorSession->getCustomerId()) {
            return null;
        }

        if ((int)$message->getIsOutbox() !== 1 || (int)$message->getIsDeleted() === 1) {
            return null;
        }

        return $message;
    }

    private function getThreadByPosition($position)
    {
        $threads = array_values($this->getThreadCollection()->getItems());
        $index = (int)$position - 1;

        return isset($threads[$index]) ? $threads[$index] : null;
    }

    private function formatThreadDate($createdAt)
    {
        try {
            $date = new \DateTime($createdAt);
        } catch (\Exception $exception) {
            return (string)$this->formatDate($createdAt, \IntlDateFormatter::MEDIUM, false);
        }

        $now = new \DateTime('now', $date->getTimezone());
        if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
            return (string)__('Today');
        }

        return $date->format('d M');
    }

    private function formatConversationDate($createdAt)
    {
        try {
            $date = new \DateTime($createdAt);
        } catch (\Exception $exception) {
            return (string)$this->formatDate($createdAt, \IntlDateFormatter::MEDIUM, true);
        }

        $now = new \DateTime('now', $date->getTimezone());
        if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
            return 'Today, ' . $date->format('d M');
        }

        return $date->format('M/d/Y');
    }

    private function formatConversationTime($createdAt)
    {
        try {
            $date = new \DateTime($createdAt);
        } catch (\Exception $exception) {
            return (string)$this->formatTime($createdAt);
        }

        return $date->format('H:i');
    }
}
