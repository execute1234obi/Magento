<?php

namespace Business\MostviewedVendors\Model\ResourceModel\Report\Collection;


class AbstractCollection extends \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection                                        
{
     
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        //\Magento\Sales\Model\ResourceModel\Report $resource,
        \Business\MostviewedVendors\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->setModel(\Magento\Reports\Model\Item::class);
        
    }
    
    
    public function addOrderStatusFilter($orderStatus)
    {
     
        return $this;
    }
    
    
    
    
    
    

    
    
}
