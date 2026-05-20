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
        $productId = (int) $this->getRequest()->getParam('product_id');
        $selectedProductId = (int) $this->getRequest()->getParam('selected_product_id');
        $baseProductId = $productId ?: $selectedProductId;
        $quoteItems = $this->catalogSession->getQuoteItems() ?: [];
        $quoteItem = [
            'product_id' => $baseProductId,
            'selected_product_id' => $selectedProductId && $selectedProductId !== $baseProductId ? $selectedProductId : 0,
            'qty' => 1
        ];

        if ($baseProductId) {
            $existingIndex = $this->findQuoteItemIndex($quoteItems, $quoteItem);

            if ($existingIndex !== null) {
                $quoteItems[$existingIndex] = $quoteItem;
            } else {
                $quoteItems[] = $quoteItem;
            }
        }

        $quoteItems = array_values($quoteItems);
        $this->catalogSession->setQuoteItems($quoteItems);

        // ✅ check if ajax
        if ($this->getRequest()->isXmlHttpRequest()) {
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData([
                'success' => true,
                'product_id' => $baseProductId,
                'selected_product_id' => $selectedProductId,
                'items' => $quoteItems,
                'count' => count($quoteItems)
            ]);
        }

        // ✅ normal request → redirect
        return $this->resultRedirectFactory
            ->create()
            ->setPath('quoterequest/view/index');
    }

    private function normalizeQuoteItem($item)
    {
        if (is_array($item)) {
            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'selected_product_id' => (int) ($item['selected_product_id'] ?? 0),
                'qty' => (int) ($item['qty'] ?? 1)
            ];
        }

        return [
            'product_id' => (int) $item,
            'selected_product_id' => 0,
            'qty' => 1
        ];
    }

    private function findQuoteItemIndex(array $quoteItems, array $quoteItem)
    {
        foreach ($quoteItems as $index => $item) {
            $storedItem = $this->normalizeQuoteItem($item);

            if ($storedItem['product_id'] === (int) $quoteItem['product_id']) {
                return $index;
            }

            if (
                !empty($quoteItem['selected_product_id']) &&
                $storedItem['selected_product_id'] === (int) $quoteItem['selected_product_id']
            ) {
                return $index;
            }
        }

        return null;
    }
}
