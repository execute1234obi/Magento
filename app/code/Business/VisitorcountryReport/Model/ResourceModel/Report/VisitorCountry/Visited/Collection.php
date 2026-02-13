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


    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        return [
            'period'       => sprintf('MAX(%s)', $connection->getDateFormatSql('period', '%Y-%m-%d')),
            'visitors_num' => 'SUM(visitors_num)',
            'country_code' => 'country_code',
            'country_id'   => 'country_id'
        ];
    }

    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();
        $mainTable = $this->getTable($this->_aggregationTable);

        $select->from(['main_table' => $mainTable], $this->_getSelectedColumns());
        
        $select->joinLeft(
            ['cm' => $this->getTable('business_visitorcountry_report_country')],
            'cm.id = main_table.country_id',
            ['country_name' => 'cm.country_name']
        );

        $select->group(['main_table.period', 'main_table.country_id']);
        return $this;
    }
}