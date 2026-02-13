<?php

namespace Business\VendorVisitorReport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_CAN_ADD_NEW_PRODUCT              = 'reports/can_view_profilevisitor_report';
    /**
     * @var \Vnecoms\VendorsGroup\Model\Config\Reader
     */
    protected $_configReader;
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory
     */
    protected $_configResourceFactory;
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\ResourceModel\Config
     */
    protected $_configResource;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Vnecoms\VendorsGroup\Model\Config\Reader $configReader
     * @param \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory $configResourceFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vnecoms\VendorsGroup\Model\Config\Reader $configReader,
        \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory $configResourceFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($context);
        $this->_configReader = $configReader;
        $this->_configResourceFactory = $configResourceFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Get Group Config
     * @return multitype
     */
    public function getGroupConfig()
    {
        $config = $this->_configReader->read();
        return $config;
    }

    /**
     * Get config by resource id and group ID
     * @param  string $resourceId
     * @param string $groupId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig($resourceId, $groupId)
    {
        if (!$this->_configResource) {
            $this->_configResource = $this->_configResourceFactory->create();
        }
        $result = $this->_configResource->getConfig($resourceId, $groupId);
        if ($result === false) {
            $result = $this->scopeConfig->getValue('vendor_advanced_group/'.$resourceId);
        }

        /*Get Config After*/
        $result = new \Magento\Framework\DataObject(['value' => $result]);
        $this->eventManager->dispatch('vendors_group_get_config_after', ['result' => $result, 'resource' => $resourceId, 'group_id' => $groupId]);
        return $result->getValue();
    }

    /**
     * @param int $groupId
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canViewProfileVisitorReport($groupId)
    {
        return $this->getConfig(self::XML_PATH_CAN_ADD_NEW_PRODUCT, $groupId);
    }

}
