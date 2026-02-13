<?php

namespace Business\VisitorcountryReport\Controller\Adminhtml\Report\Country;


use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Business\VisitorcountryReport\Model\Flag;

class Visited extends \Business\VisitorcountryReport\Controller\Adminhtml\Report\Countryvisited implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Business_VisitorcountryReport::visitorcountry';

    /**
     * visitor Country
     *
     * @return void
     */
    public function execute()
    {

        try {
		     
           // $this->_showLastExecutionTime(Flag::REPORT_VISITORCOUNTRY_VISITED_FLAG_CODE, 'visited');
           // Is line ko update karein:
$this->_showLastExecutionTime(Flag::REPORT_VISITORCOUNTRY_VISITED_FLAG_CODE, Flag::REPORT_VISITORCOUNTRY_VISITED_FLAG_CODE); 
           
           $this->_initAction()->_setActiveMenu(
                'Business_VisitorcountryReport::report_visitorcountry_visited'
            )->_addBreadcrumb(
                __('Visitor Country Report'),
                __('Visitor Country Report')
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Visitor Country Report'));

            $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_visitorcountry_visited.grid');
            $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

            $this->_initReportAction([$gridBlock, $filterFormBlock]);

            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
			//die($e->getMessage());
            $this->messageManager->addError(
                __('An error occurred while showing the product views report. Please review the log and try again.')
            );            
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('visitorcountryreport/report_country/visited/');            
            return;
        }
    }
}
