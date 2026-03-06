<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Model\ResourceModel\Transaction\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * App page collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager
    ) {
        $mainTable = 'ves_vendor_membership_transaction';
        $resourceModel = 'Vnecoms\VendorsMembership\Model\ResourceModel\Transaction';
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _construct()
    {
        parent::_construct();
        $fields = [
            'created_at',
            'vendor_id'
        ];
        foreach($fields as $field){
            $this->addFilterToMap(
                $field,
                'main_table.'.$field
            );
        }
    }
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->join(
            ['vendor'=>$this->getTable('ves_vendor_entity')],
            'vendor.entity_id = main_table.vendor_id',
            ['vendor' => 'vendor_id'],
            null,
            'left'
        );
        return $this;
    }
}
