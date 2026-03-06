<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsConfig\Helper\Data;

class ProcessMenuConfig implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManage;
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_session;

    /**
     * ProcessMenuConfig constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Module\Manager $moduleManage
     * @param \Vnecoms\Vendors\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Module\Manager $moduleManage,
        \Vnecoms\Vendors\Model\Session $session
    ) {
        $this->_objectManager = $objectmanager;
        $this->_moduleManage    = $moduleManage;
        $this->_session = $session;
    }

    /**
     * Add multiple vendor order row for each vendor.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $resource = $observer->getResource();
        $result = $observer->getResult();

        if($resource == "Vnecoms_VendorsMessage::messages"){
            $vendor = $this->_session->getVendor();

            if ($this->_moduleManage->isEnabled("Vnecoms_VendorsGroup")) {
                $groupHelper = $this->_objectManager->create("\Vnecoms\VendorsGroup\Helper\Data");
                $configVal = $groupHelper->canUseMessage($vendor->getGroupId());
                if (!$configVal) $result->setIsAllowed(false);
            }

        }

        return $this;
    }
}
