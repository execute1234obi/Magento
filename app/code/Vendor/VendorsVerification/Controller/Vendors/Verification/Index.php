<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

class Index  extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';    
    
    /**
     * @return void
     */
    public function execute()
    { 
        
        // echo "CONTROLLER WORKING";
    //die;
        $this->_initAction();
        $title = $this->_view->getPage()->getConfig()->getTitle();
        ///$this->setActiveMenu('Vnecoms_Vendors::vendorverification_manage');
        $title->prepend(__("Seller"));        
        $title->prepend(__("Seller Verification"));        
        $this->_addBreadcrumb(__("Verification"), __("Verification"));
        //echo "vrushali";
         $this->_view->renderLayout();        
    }
}
