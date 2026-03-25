<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Controller\Result\JsonFactory;

class Create extends Action
{
    protected $catalogSession;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        CatalogSession $catalogSession,
        JsonFactory $resultJsonFactory
    ) {
        $this->catalogSession = $catalogSession;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

public function execute()
{
    $resultJson = $this->resultJsonFactory->create();

    $productId = (int)$this->getRequest()->getParam('product_id');

    $quoteItems = $this->catalogSession->getQuoteItems() ?: [];

    if ($productId && !in_array($productId, $quoteItems)) {
        $quoteItems[] = $productId;
    }

    $this->catalogSession->setQuoteItems($quoteItems);

    return $resultJson->setData([
        'success' => true,
        'product_id' => $productId,
        'items' => $quoteItems,
        'count' => count($quoteItems) // ✅ IMPORTANT
    ]);
}

}