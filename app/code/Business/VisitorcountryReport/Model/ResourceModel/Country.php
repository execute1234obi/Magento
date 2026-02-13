<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VisitorcountryReport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Country extends AbstractDb
{
     /**
	 * Define main table
	*/
	protected function _construct()
	{
	   $this->_init('business_visitorcountry_report_country','id');
	 }
	
}
