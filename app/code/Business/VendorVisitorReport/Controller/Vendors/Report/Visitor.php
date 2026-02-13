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

        $resultPage->getConfig()->getTitle()->prepend(__('Profile Visitors'));

        return $resultPage;
    }
}
