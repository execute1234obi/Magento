<?php

namespace Vendor\VendorsVerification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;


class View extends Action
{
    
    
    /**
     * Page result factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Page factory
     *
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;
    
    protected $_coreRegistry;
    
    protected $vendorsVerificationFactory;
    
    protected $helper;

    /**
     * constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        VendorVerificationFactory $vendorsVerificationFactory,
        \Vendor\VendorsVerification\Helper\Data  $helper       
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|Page
     */
    public function execute()
    {
		$data = $this->getRequest()->getPost();            
        $verificationId =  $this->getRequest()->getParam('id');                    
        $verification = $this->vendorsVerificationFactory->create()->load($verificationId);        
        $verificationIncId = $verification->getIncId();
        $this->_coreRegistry->register('vendor_verification', $verification);
        $resultPage = $this->resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Seller Verification # %1',$verificationIncId)));        
        return $resultPage;
    }
    
     public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vendor_VendorsVerification::vendor_verification_manage');
    }

}

