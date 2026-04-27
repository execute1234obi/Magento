<?php
namespace Vendor\QuoteRequest\Controller\Customer;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vendor\QuoteRequest\Model\ResourceModel\QuoteItem\CollectionFactory as ItemCollectionFactory;

class ReRequest extends Action implements HttpGetActionInterface
{
    protected $catalogSession;
    protected $quoteFactory;
    protected $itemCollectionFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        CatalogSession $catalogSession,
        QuoteFactory $quoteFactory,
        ItemCollectionFactory $itemCollectionFactory,
        CustomerSession $customerSession
    ) {
        $this->catalogSession = $catalogSession;
        $this->quoteFactory = $quoteFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
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

        $items = $this->itemCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->setOrder('item_id', 'ASC');

        $productIds = [];
        foreach ($items as $item) {
            $productId = (int) $item->getProductId();
            if ($productId > 0) {
                $productIds[] = $productId;
            }
        }

        $this->catalogSession->setQuoteItems(array_values(array_unique($productIds)));
        $this->messageManager->addSuccessMessage(__('The products were added to a new quotation request.'));

        return $this->resultRedirectFactory->create()->setPath('quoterequest/view/index');
    }
}
