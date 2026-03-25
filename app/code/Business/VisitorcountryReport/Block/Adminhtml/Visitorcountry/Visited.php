<?php
namespace Business\VisitorcountryReport\Block\Adminhtml\Visitorcountry;

class Visited extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    protected function _construct()
    {
        $this->_blockGroup = 'Business_VisitorcountryReport';
        $this->_controller = 'adminhtml_visitorcountry_visited';
        $this->_headerText = __('Visitor Country');

        parent::_construct();

        $this->buttonList->remove('add');

        $this->addButton(
            'filter_form_submit',
            [
                'label' => __('Show Report'),
                'onclick' => 'filterFormSubmit()',
                'class' => 'primary'
            ]
        );
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/*', ['_current' => true]);
    }
}
