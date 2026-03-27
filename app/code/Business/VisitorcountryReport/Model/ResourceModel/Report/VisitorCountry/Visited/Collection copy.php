<?php
namespace Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Visited;

class Collection extends \Business\VisitorcountryReport\Model\ResourceModel\Report\Collection\AbstractCollection
{
    protected $_aggregationTable = 'business_visitor_country_aggregated';
    protected $_ratingLimit = 5;

 public function __construct(
    \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
    \Magento\Framework\Event\ManagerInterface $eventManager,
    \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
    \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
) {
    parent::__construct(
        $entityFactory,
        $logger,
        $fetchStrategy,
        $eventManager,
        $connection,
        $resource
    );
}

    protected function _construct()
{
    $this->_setIdFieldName('country_id');
}

/**
     * Reports engine isi method se data fetch karta hai
     */
    protected function _initSelect()
    {
        $this->_applyAggregatedTable();
        return $this;
    }
protected function _getSelectedColumns()
{
    return [
        'period'       => 'main_table.period',
        'visitors_num' => 'SUM(main_table.visitors_num)',
        'country_code' => 'main_table.country_code',
        'country_id'   => 'main_table.country_id'
    ];
}

protected function _applyAggregatedTable()
{
    $select = $this->getSelect();
    $mainTable = $this->getTable($this->_aggregationTable);

    $select->from(['main_table' => $mainTable], $this->_getSelectedColumns());

    $select->joinLeft(
        ['cm' => $this->getTable('business_visitorcountry_report_country')],
        'cm.country_id = main_table.country_code',
        ['country_name' => 'cm.country_name']
    );

    $select->group(['main_table.period', 'main_table.country_code']);

    return $this;
}


}