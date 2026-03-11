<?php
namespace Vendor\QuoteRequest\Controller\Vendors\Rfq;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vnecoms\Vendors\Model\Session as VendorSession;

class Productview extends Action
{
    protected $resultPageFactory;
    protected $quoteFactory;
    protected $vendorSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteFactory $quoteFactory,
        VendorSession $vendorSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteFactory = $quoteFactory;
        $this->vendorSession = $vendorSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $vendorId = $this->vendorSession->getVendor()->getId();

        $quote = $this->quoteFactory->create()->load($quoteId);

        if (!$quote->getId() || $quote->getVendorId() != $vendorId) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Invalid RFQ'));
        }

        return $this->resultPageFactory->create();
    }
}