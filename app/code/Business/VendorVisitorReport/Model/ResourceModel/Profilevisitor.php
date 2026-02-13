<?php
namespace Business\VendorVisitorReport\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Profilevisitor extends AbstractDb
{
    /**
	 * Define main table
	*/
	protected function _construct()
	{

	        $this->_init('business_vendor_mostview_aggregated','id');

	 }
}

