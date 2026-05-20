<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Session as CatalogSession;

class Remove extends Action
{
    protected $catalogSession;

    public function __construct(
        Context $context,
        CatalogSession $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
        parent::__construct($context);
    }

    // public function execute()
    // {
    //     $productId = (int)$this->getRequest()->getParam('id');
    //     $quoteItems = $this->catalogSession->getQuoteItems() ?: [];

    //     // Array mein se ID search karke remove karein
    //     if (($key = array_search($productId, $quoteItems)) !== false) {
    //         unset($quoteItems[$key]);
    //     }

    //     // Session update karein (array_values se indexing reset ho jati hai)
    //     $this->catalogSession->setQuoteItems(array_values($quoteItems));

    //     // Wapas Quote page par bhej dein
    //     $resultRedirect = $this->resultRedirectFactory->create();
    //     return $resultRedirect->setPath('quoterequest/view/index');
    // }
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $quoteItems = $this->catalogSession->getQuoteItems() ?: [];

        foreach ($quoteItems as $index => $item) {
            $storedItem = $this->normalizeQuoteItem($item);

            if (
                $storedItem['product_id'] === $productId ||
                $storedItem['selected_product_id'] === $productId
            ) {
                unset($quoteItems[$index]);
                break;
            }
        }

        $this->catalogSession->setQuoteItems(array_values($quoteItems));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('quoterequest/view/index');
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
}
