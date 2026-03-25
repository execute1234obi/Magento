<?php
namespace Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry;

class Visited extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
    protected function _construct()
    {
        // Database table name
        $this->_init('business_visitor_country_aggregated', 'id');
    }

    protected $_isAggregated = true;

    public function getReportTitle()
    {
        die("Test Reach getReportTitle");
        return __('Visitor Country Report');
    }

    public function aggregate($from = null, $to = null)
    {
        // Aapka purana aggregation logic yahan aayega
        return $this;
    }
}