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
    $productId = (int)$this->getRequest()->getParam('id');

    $quoteItems = $this->catalogSession->getQuoteItems() ?: [];

    echo "Removing product ID: " . $productId;
    print_r($quoteItems);

    if (($key = array_search($productId, $quoteItems)) !== false) {

        unset($quoteItems[$key]);

    }

    // reindex
    $quoteItems = array_values($quoteItems);

    print_r($quoteItems); // debug
    //exit;
    $this->catalogSession->setQuoteItems($quoteItems);

    $resultRedirect = $this->resultRedirectFactory->create();
    return $resultRedirect->setPath('quoterequest/view/index');
}
}