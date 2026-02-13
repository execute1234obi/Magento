<?php
namespace Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry;


class Updatedat extends Createdat
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('business_visitor_country_aggregated', 'id');
    }

     /**
     * Aggregate data for cron execution
     *
     * @param string|int|\DateTime|null $from
     * @param string|int|\DateTime|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        return parent::aggregate($from, $to);
    }
}
