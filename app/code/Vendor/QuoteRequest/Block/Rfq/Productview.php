<?php
namespace Vendor\QuoteRequest\Block\Rfq;

use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vendor\QuoteRequest\Model\ResourceModel\QuoteItem\CollectionFactory as ItemCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Productview extends Template
{
    protected $quoteFactory;
    protected $itemCollectionFactory;
protected $productRepository;
    public function __construct(
        Template\Context $context,
        QuoteFactory $quoteFactory,
        ItemCollectionFactory $itemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
         $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function getQuote()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->quoteFactory->create()->load($id);
    }

    public function getItems()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->itemCollectionFactory->create()
            ->addFieldToFilter('quote_id', $id);
    }

    public function getProductName($productId)
{
    try {
        $product = $this->productRepository->getById($productId);
        
        //echo "<pre>";
        //print_r($product->getData());
        //exit;

        return $product->getName();
    } catch (\Exception $e) {
        return 'Product Not Found';
    }
}
public function getProductUrl($productId)
{
    try {
        $product = $this->productRepository->getById($productId);
        return $product->getProductUrl();
    } catch (\Exception $e) {
        return '#';
    }
}
}