<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\VendorVisitorReport\Block\Vendor;


class Visitor extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'Business_VendorVisitorReport::report/grid/container.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Reports:  Profile Visitore';
        $this->_controller = 'vendor_vendorreports_report_visitor';
        $this->_headerText = __('Reports: Profile Visitor');
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

