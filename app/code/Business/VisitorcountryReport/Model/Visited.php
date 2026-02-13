<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VisitorcountryReport\Model;


class Visited extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'business_visitor_country_report';
    
    /**
	* Define resource model
	*/
	protected function _construct()
	{
			$this->_init('Business\VisitorcountryReport\Model\ResourceModel\Visited');

	}    
}
