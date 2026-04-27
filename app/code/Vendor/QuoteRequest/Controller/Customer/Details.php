<?php
namespace Vendor\QuoteRequest\Controller\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Vendor\QuoteRequest\Model\QuoteFactory;

class Details extends Action implements HttpGetActionInterface
{
    protected $resultPageFactory;
    protected $quoteFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QuoteFactory $quoteFactory,
        CustomerSession $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteFactory = $quoteFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $quoteId = (int) $this->getRequest()->getParam('quote_id', $this->getRequest()->getParam('id'));
        $customerId = (int) $this->customerSession->getCustomerId();

        if ($quoteId <= 0 || $customerId <= 0) {
            throw new NotFoundException(__('Invalid RFQ.'));
        }

        $quote = $this->quoteFactory->create()->load($quoteId);
        if (!$quote->getId() || (int) $quote->getCustomerId() !== $customerId) {
            throw new NotFoundException(__('Invalid RFQ.'));
        }

        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('Quotation Request Details'));

        return $page;
    }
}
