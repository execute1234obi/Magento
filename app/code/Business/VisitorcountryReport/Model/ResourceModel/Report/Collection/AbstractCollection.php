<?php

namespace Business\VisitorcountryReport\Model\ResourceModel\Report\Collection;

use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AbstractCollection extends
    \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection
{
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,        // ✅ 5th MUST be connection
        AbstractDb $resource = null                 // ✅ 6th MUST be resource
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->setModel(\Magento\Reports\Model\Item::class);
    }

    public function addOrderStatusFilter($orderStatus)
    {
        return $this;
    }
}
