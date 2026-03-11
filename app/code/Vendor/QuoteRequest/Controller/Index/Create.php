<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Session as CatalogSession;

class Create extends Action
{
    protected $catalogSession;

    public function __construct(
        Context $context,
        CatalogSession $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
        parent::__construct($context);
    }

    public function execute()
    {
       $productId = (int)$this->getRequest()->getParam('product_id');
        
        // Debugging ke liye: 
        // if (!$productId) { die("Product ID missing in request!"); }

        $quoteItems = $this->catalogSession->getQuoteItems() ?: [];

        if ($productId && !in_array($productId, $quoteItems)) {
            $quoteItems[] = $productId;
        }

        $this->catalogSession->setQuoteItems($quoteItems);

        return $this->resultRedirectFactory->create()->setPath('quoterequest/view/index');
    }
}
