<?php

namespace Gcc\VendorProductOverride\Block\Product;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class SameVendorProducts extends Template
{
    private $collectionFactory;
    private $registry;
    private $imageBuilder;
    private $pricingHelper;
    private $visibility;
    private $storeManager;
    private $productCollection = null;

    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        Registry $registry,
        ImageBuilder $imageBuilder,
        PricingHelper $pricingHelper,
        Visibility $visibility,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->imageBuilder = $imageBuilder;
        $this->pricingHelper = $pricingHelper;
        $this->visibility = $visibility;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    public function getItems()
    {
        return $this->getProductCollection();
    }

    public function getProductCollection()
    {
        if ($this->productCollection instanceof Collection) {
            return $this->productCollection;
        }

        $currentProduct = $this->registry->registry('product');
        if (!$currentProduct || !$currentProduct->getId()) {
            return $this->productCollection = $this->createEmptyCollection();
        }

        $vendorId = (int) $currentProduct->getVendorId();
        if (!$vendorId) {
            return $this->productCollection = $this->createEmptyCollection();
        }

        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addUrlRewrite();
        $collection->addAttributeToSelect([
            'name',
            'price',
            'image',
            'small_image',
            'thumbnail',
            'vendor_id',
            'manufacturer',
        ]);
        $collection->addAttributeToFilter('vendor_id', $vendorId);
        $collection->addAttributeToFilter('entity_id', ['neq' => (int) $currentProduct->getId()]);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addFinalPrice();
        $collection->addMinimalPrice();
        $collection->setVisibility($this->visibility->getVisibleInSiteIds());
        $collection->setPageSize((int) ($this->getData('limit') ?: 8));
        $collection->setCurPage(1);
        $collection->setOrder('entity_id', 'DESC');

        return $this->productCollection = $collection;
    }

    public function getImage($product, $imageId = 'category_page_list')
    {
        return $this->imageBuilder
            ->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }

    public function getProductPrice($product)
    {
        $price = $this->pricingHelper->currency((float) $product->getFinalPrice(), true, false);
        return '<span class="price">' . $price . '</span>';
    }

    private function createEmptyCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => [0]]);
        return $collection;
    }
}
