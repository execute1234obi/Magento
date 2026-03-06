<?php
namespace Vendor\QuoteRequest\Block;

use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class View extends Template
{
    protected $quoteCollectionFactory;
    protected $session;
    protected $productRepository;

    public function __construct(
        Template\Context $context,
        QuoteCollectionFactory $quoteCollectionFactory,
        CustomerSession $session,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->session = $session;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function getQuotes()
    {
        return $this->quoteCollectionFactory->create();
    }

    public function getQuoteItems()
    {
        // Get session-stored cart (or empty array)
        $quoteCart = $this->session->getQuoteItems() ?? [];
        $items = [];

        foreach ($quoteCart as $item) {
            try {
                $product = $this->productRepository->getById($item['product_id']);
                $items[] = [
                    'product' => $product,
                    'qty' => $item['qty']
                ];
            } catch (\Exception $e) {
                continue; // skip invalid products
            }
        }

        return $items;
    }
}
