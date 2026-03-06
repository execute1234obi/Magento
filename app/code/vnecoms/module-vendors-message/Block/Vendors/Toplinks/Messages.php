<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Vendors\Toplinks;

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Vendor Notifications block
 */
class Messages extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
    /**
     * @var \Vnecoms\VendorsMessage\Model\MessageFactory
     */
    protected $_messageFactory;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    protected $_unreadMessageCollection;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $helperData;

    /**
     * @var CustomerRepositoryInterface |
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManage;

    /**
     * Messages constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Vnecoms\Vendors\Model\UrlInterface $url
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Vnecoms\VendorsMessage\Helper\Data $helperData
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Module\Manager $moduleManage
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        CustomerRepositoryInterface $customerRepository,
        \Vnecoms\VendorsMessage\Helper\Data $helperData,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Module\Manager $moduleManage,
        array $data = []
    ) {
        parent::__construct($context, $url, $data);
        $this->_messageFactory = $messageFactory;
        $this->_vendorSession = $vendorSession;
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
        $this->_objectManager = $objectmanager;
        $this->_moduleManage    = $moduleManage;
    }
    
    /**
     * Get Pending Credit URL
     * 
     * @return string
     */
    public function getPendingCreditUrl(){
        return $this->getUrl('credit/pending');
    }
    
    /**
     * Get Unread Message Collection
     * 
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    public function getUnreadMessageCollection(){
        if(!$this->_unreadMessageCollection){
            $this->_unreadMessageCollection = $this->_messageFactory->create()->getCollection();
            $this->_unreadMessageCollection->addFieldToFilter('owner_id',$this->_vendorSession->getCustomerId())
                ->addFieldToFilter('status',\Vnecoms\VendorsMessage\Model\Message::STATUS_UNDREAD)
                ->addFieldToFilter('is_inbox',1)
                ->addFieldToFilter('is_deleted',0)
                ->setOrder('message_id','DESC')
                ->setPageSize(5);
        }
        
        return $this->_unreadMessageCollection;
    }
    
    
    /**
     * Get Unread Message Count
     * 
     * @return int
     */
    public function getUnreadMessageCount(){
       return $this->getUnreadMessageCollection()->getSize();
    }
    
    /**
     * Format message time
     * 
     * @param string $dateTime
     */
    public function getMessageTime($dateTime){
        $messageTimeStamp = strtotime($dateTime);
        $timeStamp = time();
        
        $differentTime = $timeStamp - $messageTimeStamp;
        $minutes = round($differentTime / 60);
        if($minutes == 0) return __("Now");
        
        elseif($minutes < 60){
            return __("%1 minutes", $minutes);
        }
        
        $hours = round($minutes / 60);
        if($hours < 24) return __("Today");
        
        $days = round($hours / 24);
        if($days == 1) return __("Yesterday");
        if($days < 7) return __("%1 days", $days);
        
        if($days < 365) return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
        
        return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
    }
    
    /**
     * Get Message URL
     * 
     * @return string
     */
    public function getMessageUrl(){
        return $this->getUrl('message');
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
        return $this->getUrl('message/view/index',['id' => $message->getId()]);
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvatarUrl($customerId)
    {
        $avatarFile = $this->getCustomer($customerId)->getCustomAttribute('profile_picture');
        $file = $avatarFile ? $avatarFile->getValue() : false;
        return $this->helperData->getAvatarOfCustomer($file);
    }


    /**
     * @return string
     */
    public function _toHtml()
    {
        $vendor = $this->_vendorSession->getVendor();

        if ($this->_moduleManage->isEnabled("Vnecoms_VendorsGroup")) {
            $groupHelper = $this->_objectManager->create("\Vnecoms\VendorsGroup\Helper\Data");
            $configVal = $groupHelper->canUseMessage($vendor->getGroupId());
            if (!$configVal) {
                return null;
            }
        }

        return parent::_toHtml(); // TODO: Change the autogenerated stub
    }
}
