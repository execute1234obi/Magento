<?php
namespace Vendor\QuoteRequest\Controller\Adminhtml\Index;

use Vnecoms\Vendors\Controller\Adminhtml\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Date $dateFilter,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry, $dateFilter);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {

        // Check karne ke liye ki kya layout handle sahi hai
    //die(print_r($this->resultPageFactory->create()->getLayout()->getUpdate()->getHandles(), true));
        $this->_initAction();

        $page = $this->resultPageFactory->create();
        $page->setActiveMenu('Vendor_QuoteRequest::rfq_history');
        $page->getConfig()->getTitle()->prepend(__('Vendor Quotes'));

        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Vendor_QuoteRequest::rfq_history'
        );
    }
}