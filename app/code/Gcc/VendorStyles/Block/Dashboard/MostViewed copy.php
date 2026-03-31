<?php

namespace Gcc\VendorStyles\Block\Dashboard; // Dashboard add kiya gaya hai

use Magento\Framework\View\Element\Template;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;

class MostViewed extends Template
{
    protected $reportCollectionFactory;
    protected $imageHelper;

    public function __construct(
        Template\Context $context,
        ReportCollectionFactory $reportCollectionFactory,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    public function getMostViewedProducts()
    {
        // Default Magento Reports Collection for most viewed
        $collection = $this->reportCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addViewsCount() 
            ->setPageSize(10)
            ->setCurPage(1);
            
        return $collection;
    }

    public function getProductImage($product)
    {
        return $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }
}