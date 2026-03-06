<?php

namespace Vnecoms\VendorsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class Predispatch implements ObserverInterface
{
    const MESSAGE_IDENTIFIER    = 'vendor_membership_notification';
    
    const SESSION_KEY           = 'vendors_profile_notification_is_shown';
    
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $session;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Url
     */
    protected $frontendUrl;
    
    /**
     * @param \Vnecoms\Vendors\Model\Session $session
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Vnecoms\Vendors\Model\Session $session,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Url $frontendUrl
    ) {
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->coreRegistry = $coreRegistry;
        $this->frontendUrl = $frontendUrl;
    }
    
    /**
     * Get vendor object
     * 
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor(){
        return $this->session->getVendor();
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->coreRegistry->registry(self::SESSION_KEY)) return;
        $this->coreRegistry->register(self::SESSION_KEY, true);
        
        if(
            $this->getVendor()->getStatus() == \Vnecoms\Vendors\Model\Vendor::STATUS_EXPIRED
        ) {
            $messages = $this->messageManager->getMessages(false)->deleteMessageByIdentifier(self::MESSAGE_IDENTIFIER);
            $this->messageManager->addError(
                __("Your membership is expired. Please renew or buy a new membership package. ").
                '<a href="'.$this->frontendUrl->getUrl('vendorsmembership').'">'. __("Click here to buy membership") .'</a>'
            );
            $this->messageManager->getMessages(false)->getLastAddedMessage()->setIdentifier(self::MESSAGE_IDENTIFIER);
        }

        return $this;
    }
}
