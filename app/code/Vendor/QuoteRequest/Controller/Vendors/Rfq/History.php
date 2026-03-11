<?php
namespace Vendor\QuoteRequest\Controller\Vendors\Rfq;

use Vnecoms\Vendors\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class History extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    // public function execute()
    // {
    //     $resultPage = $this->resultPageFactory->create();
    //     $resultPage->getConfig()->getTitle()->set(__('RFQ History'));
    //     return $resultPage;
    // }

    public function execute()
{
    $resultPage = $this->resultPageFactory->create();
    //echo get_class($this); die();
    // Ye line aapko batayegi ki exactly kaunsi file ka naam hona chahiye
    // Page load hote hi screen par handles ki list aa jayegi
   // die(implode(', ', $resultPage->getLayout()->getUpdate()->getHandles()));
  // $handles = $resultPage->getLayout()->getUpdate()->getHandles();
//echo "<pre>";
//print_r($handles);
//exit;
    $resultPage->getConfig()->getTitle()->set(__('RFQ History'));
    return $resultPage;
}
}