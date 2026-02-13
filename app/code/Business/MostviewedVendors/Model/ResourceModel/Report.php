<?php

namespace Business\MostviewedVendors\Model\ResourceModel;

//use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

//class Report extends AbstractDb
class Report extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
		
    /**
	 * Define main table
	*/
	/*protected function _construct()
	{
	   $this->_init('business_vendor_mostview_aggregated','id');
	 }*/
	 
	 /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
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

