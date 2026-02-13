<?php
namespace Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry;

class Createdat extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    protected function _construct()
    {
        $this->_init('business_visitor_country_aggregated', 'id');
    }

    public function aggregate($from = null, $to = null)
{
    $connection = $this->getConnection();
    $connection->beginTransaction();

    try {
        $this->_clearTableByDateRange($this->getMainTable(), $from, $to);

       // $periodExpr = $connection->getDateFormatSql('logged_at', '%Y-%m-%d');
        $periodExpr = new \Zend_Db_Expr("DATE(source_table.logged_at)");

        $select = $connection->select()
            ->from(
                ['source_table' => $this->getTable('report_event')],
                [
                    'period'       => $periodExpr,
                    'store_id'     => 'source_table.store_id',
                    'country_id'   => 'source_table.object_id',
                    'country_code' => 'country_master.country_id',
                    'visitors_num' => new \Zend_Db_Expr('COUNT(*)')
                ]
            )
            ->joinInner(
                ['country_master' => $this->getTable('business_visitorcountry_report_country')],
                'country_master.id = source_table.object_id',
                []
            )
            ->where('source_table.event_type_id = ?', 7)
            ->group([$periodExpr, 'source_table.store_id', 'source_table.object_id']);

        $insertQuery = $connection->insertFromSelect(
            $select,
            $this->getMainTable(),
            ['period', 'store_id', 'country_id', 'country_code', 'visitors_num']
        );
        \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info((string)$insertQuery);
        $connection->query($insertQuery);
        $connection->commit();

    } catch (\Exception $e) {
        $connection->rollBack();
        throw $e;
    }

    return $this;
}

}