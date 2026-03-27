<?php

namespace Vendor\QuoteRequest\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class History extends Template
{
    protected $customerSession;
    protected $resource;
    protected $productRepository;
    protected $imageHelper;
    protected $priceHelper;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository,
       Image $imageHelper,
       PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->resource = $resource;
        $this->productRepository = $productRepository;
          $this->imageHelper = $imageHelper;
           $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return (int) $this->customerSession->getCustomerId();
    }

    public function getRfqs()
{
    $connection = $this->resource->getConnection();

    $quoteTable  = $this->resource->getTableName('vendor_quote');
    $detailTable = $this->resource->getTableName('vendor_quote_item');

    $select = $connection->select()
        ->from(['q' => $quoteTable])
        ->joinLeft( // safer than join()
            ['d' => $detailTable],
            'q.quote_id = d.quote_id',
            ['product_id', 'qty']
        )
        ->where('q.customer_id = ?', $this->getCustomerId())
        ->order('q.created_at DESC');

    return $connection->fetchAll($select);
}
    public function getProduct($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return null;
        }
    }
public function getImageUrl($product)
{
    if (!$product || !$product->getId()) {
        return null;
    }

    return $this->imageHelper
        ->init($product, 'product_thumbnail_image')
        ->getUrl();
}
public function formatPrice($price)
{
    if ($price === null || $price === '') {
        return '-';
    }

    return $this->priceHelper->currency($price, true, false);
}


}
