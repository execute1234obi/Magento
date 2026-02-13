<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VisitorcountryReport\Model\ResourceModel\Visited;

//use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zend_Db_Select;


class Collection extends AbstractCollection
{

    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'index_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'business_visitor_country_report_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'business_visitorcountry_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Business\VisitorcountryReport\Model\Visited', 'Business\VisitorcountryReport\Model\ResourceModel\Visited');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }   
	
}
