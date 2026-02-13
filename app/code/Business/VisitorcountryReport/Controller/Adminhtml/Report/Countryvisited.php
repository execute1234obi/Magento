<?php
namespace Business\VisitorcountryReport\Controller\Adminhtml\Report;


abstract class Countryvisited extends \Magento\Reports\Controller\Adminhtml\Report\AbstractReport

{
    /**
     * Add report/products breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Country'), __('visited'));
        return $this;
    }
}
