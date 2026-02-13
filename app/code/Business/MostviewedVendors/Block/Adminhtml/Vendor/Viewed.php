<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\MostviewedVendors\Block\Adminhtml\Vendor;


class Viewed extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Business_MostviewedVendors';
        $this->_controller = 'adminhtml_vendor_viewed';
        $this->_headerText = __('Most Viewed');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter url
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/viewed', ['_current' => true]);
    }
}
