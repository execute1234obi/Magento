<?php

namespace Business\MostviewedVendors\Block\Adminhtml\Vendor\Viewed;

/**
 * Adminhtml most viewed products report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';    
    
    /**
     * Grid resource collection name
     *
     * @var string
     */   
   protected $_resourceCollectionName = \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed\Collection::class;
    //protected $_resourceCollectionName = \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection::class;


    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(false);
    }
    
    /*public function getCollection()
    {
        if ($this->_collection === null) {
            $this->setCollection($this->_collectionFactory->create());
        }
        return $this->_collection;
    }*/

    /**
     * {@inheritdoc}
     */
    /*public function getResourceCollectionName()
    {        
        return \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed\Collection::class;
    }*/

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        
        
        $this->addColumn(
            'period',
            [
                'header' => __('Interval'),
                'index' => 'period',
                'width' => 100,
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'vendor_id',
            [
                'header' => __('Vendor ID'),
                'sortable' => false,
                'index' => 'vendor_id',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        
        $this->addColumn(
            'vendor_code',
            [
                'header' => __('Vendor Code'),
                'sortable' => false,
                'index' => 'vendor_code',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        
        $this->addColumn(
            'views_num',
            [
                'header' => __('Views'),
                'index' => 'views_num',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );




        $this->addExportType('*/*/ExportViewedCsv', __('CSV'));
        $this->addExportType('*/*/ExportViewedExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Add price rule filter
     *
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection $collection
     * @param \Magento\Framework\DataObject $filterData
     * @return \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        /*if ($filterData->getPriceRuleType()) {
            $rulesList = $filterData->getData('rules_list');
            if (isset($rulesList[0])) {
                $rulesIds = explode(',', $rulesList[0]);
                $collection->addRuleFilter($rulesIds);
            }
        }*/

        return parent::_addCustomFilter($filterData, $collection);
    }
}
