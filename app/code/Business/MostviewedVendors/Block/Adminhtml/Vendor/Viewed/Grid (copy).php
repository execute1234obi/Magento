<?php

namespace Business\MostviewedVendors\Block\Adminhtml\Vendor\Viewed;

/**
 * Adminhtml most viewed vendor profile report grid block
 * 
 */
//class Grid extends \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection	
//class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
//class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid

{
    /**
     * Column for grid to be grouped by
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';
    
    protected $_resourceCollectionName = \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed\Collection::class;
    //protected $_resourceCollectionName = \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection::class;

    /**
     * Grid resource collection name
     *
     * @var string
     */
    //protected $_resourceCollectionName = \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed\Collection::class;
    
    /**
     * @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $_vendorsFactory;

    /**
     * Init grid parameters
     *
     * @return void
     */
    /*public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory $vendorsFactory,
        array $data = []
    ) {
        $this->_vendorsFactory = $vendorsFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mostviewedvendorReportGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }*/
    
    /**
     * Init grid parameters
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }
    
     public function getResourceCollectionName()
    {
        return \Magento\Reports\Model\ResourceModel\Report\Product\Viewed\Collection::class;
    }


    /**
     * @return $this
     */
    /*protected function _prepareCollection()
    {
        $collection = $this->_vendorsFactory->create();

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }*/
    
    /*public function getResourceCollectionName()
    {
    
        return \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\Viewed\Collection::class;
    }*/


    /**
     * Custom columns preparation
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        /*$this->addColumn(
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
        );*/

        /*$this->addColumn(
            'vendor_id',
            [
                'header' => __('Vendor Id'),
                'index' => 'vendor_id',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );*/
        
        $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );


        /*if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'product_price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'product_price',
                'sortable' => false,
                'rate' => $this->getRate($currencyCode),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
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
        );*/

        $this->addExportType('*/*/exportViewedCsv', __('CSV'));
        $this->addExportType('*/*/exportViewedExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }


}
