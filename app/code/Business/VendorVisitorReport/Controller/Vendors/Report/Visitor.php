<?php

namespace Business\VendorVisitorReport\Controller\Vendors\Report;

use Vnecoms\Vendors\Controller\Vendors\Action;
use Magento\Framework\Controller\ResultFactory;

class Visitor extends Action
{
    protected $_aclResource = 'Business_VendorVisitorReport::vendorvisitorreport_manage';

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->set(__('Reports'));

        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addLink(__('Reports'), __('Reports'));
            $breadcrumbs->addLink(__('Profile Visitors'), __('Profile Visitors'));
        }

        return $resultPage;
    }
}
