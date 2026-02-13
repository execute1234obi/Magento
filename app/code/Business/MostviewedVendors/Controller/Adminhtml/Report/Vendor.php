<?php
namespace Business\MostviewedVendors\Controller\Adminhtml\Report;


abstract class Vendor extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport

{
    /**
     * Add report/products breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Vendors'), __('Vendors'));
        return $this;
    }
}
