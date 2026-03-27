<?php

namespace Business\VisitorcountryReport\Block\Adminhtml\Visitorcountry\Visited;

class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    protected $_columnGroupBy = 'period';

    protected $_resourceCollectionName = 'Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Visited\Collection';

    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(false);
    }

    protected function _prepareCollection()
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create($this->_resourceCollectionName);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

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
                'header_css_class' => 'col-country',
                'column_css_class' => 'col-country'
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

    protected function _addCustomFilter($collection, $filterData)
    {
        return parent::_addCustomFilter($collection, $filterData);
    }
}
