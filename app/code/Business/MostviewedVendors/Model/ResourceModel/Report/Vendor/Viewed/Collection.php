<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed;

//class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
//class Collection extends  \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection                          
class Collection extends \Business\MostviewedVendors\Model\ResourceModel\Report\Collection\AbstractCollection                          
{

    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'business_vendor_mostview_aggregated';


    /**
     * Rating limit
     *
     * @var int
     */
    protected $_ratingLimit = 5;

    /**
     * Selected columns
     *
     * @var array
     */
    protected $_selectedColumns = [];


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
        \Business\MostviewedVendors\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        
        $resource->init($this->_aggregationTable);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * Return ordered filed
     *
     * @return string
     */
    protected function getOrderedField()
    {
        return 'views_num';
    }

    /**
     * Return table per period
     *
     * @param string $period
     * @return mixed
     */
    public function getTableByAggregationPeriod($period)
    {
        return $this->_aggregationTable;
    }

    /**
     * Retrieve selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();

        if (!$this->_selectedColumns) {
            if ($this->isTotals()) {
                $this->_selectedColumns = $this->getAggregatedColumns();
            } else {
                $this->_selectedColumns = [
                    'period' => sprintf('MAX(%s)', $connection->getDateFormatSql('period', '%Y-%m-%d')),
                    $this->getOrderedField() => 'SUM(' . $this->getOrderedField() . ')',
                    'vendor_id' => 'vendor_id',
                    'vendor_code' => 'vendor_code',
                    
                ];
                if ('year' == $this->_period) {
                    $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y');
                } elseif ('month' == $this->_period) {
                    $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y-%m');
                }
            }
        }
        /*$this->_selectedColumns = [
                    'period' => sprintf('MAX(%s)', $connection->getDateFormatSql('period', '%Y-%m-%d')),
                    $this->getOrderedField() => 'SUM(' . $this->getOrderedField() . ')',
                    'vendor_id' => 'vendor_id',
                    'vendor_code' => 'vendor_code',
                    
                ];*/
        //echo "<pre>".print_r($this->_selectedColumns,1)."</pre>";
        
        return $this->_selectedColumns;
    }

    /**
     * Make select object for date boundary
     *
     * @param string $from
     * @param string $to
     * @return \Magento\Framework\DB\Select
     */
    protected function _makeBoundarySelect($from, $to)
    {
        $connection = $this->getConnection();
        $cols = $this->_getSelectedColumns();
        $cols[$this->getOrderedField()] = 'SUM(' . $this->getOrderedField() . ')';
        $select = $connection->select()->from(
            $this->getResource()->getMainTable(),
            $cols
        )->where(
            'period >= ?',
            $from
        )->where(
            'period <= ?',
            $to
        )->group(
            'vendor_id'
        )->order(
            $this->getOrderedField() . ' DESC'
        )->limit(
            $this->_ratingLimit
        );

        $this->_applyStoresFilterToSelect($select);
        
        //echo "3)<br />".$select."<br />";

        return $select;
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();

        //if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();
            $cols[$this->getOrderedField()] = 'SUM(' . $this->getOrderedField() . ')';
            /*if ($this->_from || $this->_to) {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
                $select->from($mainTable, $cols);
                
            } else {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
                $select->from($mainTable, $cols);
                
            }*/
            
            $mainTable = $this->getTable($this->_aggregationTable);
                $select->from($mainTable, $cols);

            //exclude removed products
            $select->order(
               $this->getOrderedField() . ' ' . \Magento\Framework\DB\Select::SQL_DESC
            )->limit(
                $this->_ratingLimit
            );

            return $this;
        }

        /*if ('year' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } elseif ('month' == $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('monthly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
            $select->from($mainTable, $this->_getSelectedColumns());
            echo "1)". $select."<br />";

        }*/
        $select->from($this->_aggregationTable, $this->_getSelectedColumns());
        //if (!$this->isTotals()) {
            $select->group(['period', 'vendor_id']);
        //}
        ///$select->where('rating_pos <= ?', $this->_ratingLimit);       
        
        //echo "2) PB <br />".$select."<br />";
        //die;


        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * Set ids for store restrictions
     *
     * @param  int|int[] $storeIds
     * @return $this
     */
    public function addStoreRestrictions($storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $currentStoreIds = $this->_storesIds;
        if (isset(
            $currentStoreIds
        ) && $currentStoreIds != \Magento\Store\Model\Store::DEFAULT_STORE_ID && $currentStoreIds != [
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ]
        ) {
            if (!is_array($currentStoreIds)) {
                $currentStoreIds = [$currentStoreIds];
            }
            $this->_storesIds = array_intersect($currentStoreIds, $storeIds);
        } else {
            $this->_storesIds = $storeIds;
        }

        return $this;
    }

    /**
     * Redeclare parent method for applying filters after parent method
     * but before adding unions and calculating totals
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $this->_applyStoresFilter();

        if ($this->_period) {
            $selectUnions = [];

            // apply date boundaries (before calling $this->_applyDateRangeFilter())
            $periodFrom = $this->_from !== null ? new \DateTime($this->_from) : null;
            $periodTo = $this->_to !== null ? new \DateTime($this->_to) : null;
            if ('year' == $this->_period) {
                if ($periodFrom) {
                    // not the first day of the year
                    if ($periodFrom->format('m') != 1 || $periodFrom->format('d') != 1) {
                        $dtFrom = clone $periodFrom;
                        // last day of the year
                        $dtTo = clone $periodFrom;
                        $dtTo->setDate($dtTo->format('Y'), 12, 31);
                        if (!$periodTo || $dtTo < $periodTo) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // first day of the next year
                            $this->_from = clone $periodFrom;
                            $this->_from->modify('+1 year');
                            $this->_from->setDate($this->_from->format('Y'), 1, 1);
                            $this->_from = $this->_from->format('Y-m-d');
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the year
                    if ($periodTo->format('m') != 12 || $periodTo->format('d') != 31) {
                        $dtFrom = clone $periodTo;
                        $dtFrom->setDate($dtFrom->format('Y'), 1, 1);
                        // first day of the year
                        $dtTo = clone $periodTo;
                        if (!$periodFrom || $dtFrom > $periodFrom) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // last day of the previous year
                            $this->_to = clone $periodTo;
                            $this->_to->modify('-1 year');
                            $this->_to->setDate($this->_to->format('Y'), 12, 31);
                            $this->_to = $this->_to->format('Y-m-d');
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same year
                    if ($periodTo->format('Y') == $periodFrom->format('Y')) {
                        $dtFrom = clone $periodFrom;
                        $dtTo = clone $periodTo;
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->format('Y-m-d'),
                            $dtTo->format('Y-m-d')
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }
            } elseif ('month' == $this->_period) {
                if ($periodFrom) {
                    // not the first day of the month
                    if ($periodFrom->format('d') != 1) {
                        $dtFrom = clone $periodFrom;
                        // last day of the month
                        $dtTo = clone $periodFrom;
                        $dtTo->modify('+1 month');
                        $dtTo->setDate($dtTo->format('Y'), $dtTo->format('m'), 1);
                        $dtTo->modify('-1 day');
                        if (!$periodTo || $dtTo < $periodTo) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // first day of the next month
                            $this->_from = clone $periodFrom;
                            $this->_from->modify('+1 month');
                            $this->_from->setDate($this->_from->format('Y'), $this->_from->format('m'), 1);
                            $this->_from = $this->_from->format('Y-m-d');
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the month
                    if ($periodTo->format('d') != $periodTo->format('t')) {
                        $dtFrom = clone $periodTo;
                        $dtFrom->setDate($dtFrom->format('Y'), $dtFrom->format('m'), 1);
                        // first day of the month
                        $dtTo = clone $periodTo;
                        if (!$periodFrom || $dtFrom > $periodFrom) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // last day of the previous month
                            $this->_to = clone $periodTo;
                            $this->_to->setDate($this->_to->format('Y'), $this->_to->format('m'), 1);
                            $this->_to->modify('-1 day');
                            $this->_to = $this->_to->format('Y-m-d');
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same month
                    if ($periodTo->format('Y') == $periodFrom->format('Y') &&
                        $periodTo->format('m') == $periodFrom->format('m')
                    ) {
                        $dtFrom = clone $periodFrom;
                        $dtTo = clone $periodTo;
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->format('Y-m-d'),
                            $dtTo->format('Y-m-d')
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }
            }

            $this->_applyDateRangeFilter();

            // add unions to select
            if ($selectUnions) {
                $unionParts = [];
                $cloneSelect = clone $this->getSelect();
                $unionParts[] = '(' . $cloneSelect . ')';
                foreach ($selectUnions as $union) {
                    $unionParts[] = '(' . $union . ')';
                }
                $this->getSelect()->reset()->union($unionParts, \Magento\Framework\DB\Select::SQL_UNION_ALL);
            }

            if ($this->isTotals()) {
                // calculate total
                $cloneSelect = clone $this->getSelect();
                $this->getSelect()->reset()->from($cloneSelect, $this->getAggregatedColumns());
            } else {
                // add sorting
                $this->getSelect()->order(['period ASC', $this->getOrderedField() . ' DESC']);
            }
        }
        
        
        //echo "3)<br /><br />". $this->getSelect()."<br /><hr/>";
         // die;

        return $this;
    }

}
