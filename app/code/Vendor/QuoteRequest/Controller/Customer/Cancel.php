<?php
namespace Vendor\QuoteRequest\Controller\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vendor\QuoteRequest\Model\ResourceModel\Quote as QuoteResource;

class Cancel extends Action implements HttpPostActionInterface
{
    protected $quoteFactory;
    protected $quoteResource;
    protected $customerSession;

    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        CustomerSession $customerSession
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
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

        $status = strtolower(trim((string) $quote->getStatus()));
        if ($status !== 'pending') {
            $this->messageManager->addNoticeMessage(__('This RFQ can no longer be cancelled.'));
            return $this->resultRedirectFactory->create()->setPath('quoterequest/customer/details', ['quote_id' => $quoteId]);
        }

        $quote->setStatus('cancelled');
        $this->quoteResource->save($quote);
        $this->messageManager->addSuccessMessage(__('RFQ cancelled successfully.'));

        return $this->resultRedirectFactory->create()->setPath('quoterequest/customer/details', ['quote_id' => $quoteId]);
    }
}
