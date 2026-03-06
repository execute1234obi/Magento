<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Block\Adminhtml\Vendor\Edit\Tab\Transaction;

/**
 * Customer Credit transactions grid
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Backend\Block\Dashboard\Grid
{
    protected $_template = 'Magento_Backend::widget/grid.phtml';
    
    /**
     * @var \Vnecoms\VendorsMembership\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $_collectionFactory;

    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Vnecoms\VendorsMembership\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $backendHelper, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('membershipTransactionsGrid');
        $this->setDefaultLimit(20);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }
    
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('vendor_id',$this->getVendor()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created at'),
                'sortable' => true,
                'type' => 'date',
                'index' => 'created_at'
            ]
        );
    
        $this->addColumn(
            'package',
            [
                'header' => __('Package'),
                'sortable' => true,
                'type' => 'text',
                'index' => 'package',
            ]
        );
    
        $baseCurrencyCode = $this->_storeManager->getStore(0)->getBaseCurrencyCode();
    
        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'sortable' => true,
                'type' => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index' => 'amount'
            ]
        );
    
        $this->addColumn(
            'duration',
            [
                'header' => __('Duration'),
                'sortable' => false,
                'filter' => false,
                'type' => 'text',
                'index' => 'duration',
                'renderer' => 'Vnecoms\VendorsMembership\Block\Adminhtml\Vendor\Edit\Tab\Transaction\Duration',
            ]
        );
    
    
        return parent::_prepareColumns();
    }
    
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getVendor()->getCustomer()->getId();
    }
    
    /**
     * Get current vendor object
     * 
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor(){
        return $this->_coreRegistry->registry('current_vendor');
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('vendors/membership_payment/grid', array('_current'=>true));
    }

}
