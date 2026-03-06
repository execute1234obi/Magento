<?php
namespace Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Visited;

class Collection extends \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Visited::class
        );
    }
}