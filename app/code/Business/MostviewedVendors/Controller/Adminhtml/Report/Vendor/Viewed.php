<?php

namespace Business\MostviewedVendors\Controller\Adminhtml\Report\Vendor;


use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
//use Magento\Reports\Model\Flag;
use Business\MostviewedVendors\Model\Flag;

class Viewed extends \Business\MostviewedVendors\Controller\Adminhtml\Report\Vendor implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Business_MostviewedVendors::viewed';

    /**
     * Most viewed products
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_showLastExecutionTime(Flag::REPORT_VNE_VENDORS_VIEWED_FLAG_CODE, 'viewed');
            
            

            $this->_initAction()->_setActiveMenu(
                'Business_MostviewedVendors::report_vendors_viewed'
            )->_addBreadcrumb(
                __('Vendors Most Viewed Profile Report'),
                __('Vendors Most Viewed Profile Report')
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendors Most Viewed Profile Report'));

            $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_vendor_viewed.grid');
            $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

            $this->_initReportAction([$gridBlock, $filterFormBlock]);

            $this->_view->renderLayout();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while showing the product views report. Please review the log and try again.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->_redirect('reports/*/viewed/');
            return;
        }
    }
}
