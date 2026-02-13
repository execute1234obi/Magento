<?php
namespace Business\VisitorcountryReport\Model\ResourceModel;

class Report extends \Magento\Reports\Model\ResourceModel\Report\AbstractReport
{
	 
	 /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        // Yahan aapki aggregated table ka naam aayega
        $this->_init('business_visitor_country_aggregated', 'id');
    }

    /**
     * Set main table and idField
     *
     * @param string $table
     * @param string $field
     * @return $this
     */
    public function init($table, $field = 'id')
    {
        $this->_init($table, $field);
        return $this;
    }
	 
	 
}

