<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VisitorcountryReport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Visited extends AbstractDb
{
     /**
	 * Define main table
	*/
	protected function _construct()
	{
	   $this->_init('business_report_visitor_country_index','index_id');
	 }
	
}
