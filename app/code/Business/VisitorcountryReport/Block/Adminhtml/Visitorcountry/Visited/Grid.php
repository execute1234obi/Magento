<?php

namespace Business\VisitorcountryReport\Block\Adminhtml\Visitorcountry\Visited;

/**
 * Adminhtml Visitor Country report grid block
 * 
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
   protected $_resourceCollectionName = \Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Visited\Collection::class;

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(false);
    }

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
            'country_code',
            [
                'header' => __('Country Code'),
                'sortable' => false,
                'index' => 'country_code',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        
        $this->addColumn(
            'country_name',
            [
                'header' => __('Country Name'),
                'sortable' => false,
                'index' => 'country_name',
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        
        $this->addColumn(
            'visitors_num',
            [
                'header' => __('Visitors Count'),
                'index' => 'visitors_num',
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
        return parent::_addCustomFilter($filterData, $collection);
    }
}
