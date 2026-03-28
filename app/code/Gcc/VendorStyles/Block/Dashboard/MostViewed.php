<?php

namespace Gcc\VendorStyles\Block\Dashboard;

use Magento\Framework\View\Element\Template;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Vnecoms\Vendors\Model\Session as VendorSession; // Vnecoms Session

class MostViewed extends Template
{
    protected $reportCollectionFactory;
    protected $imageHelper;
    protected $vendorSession;

    public function __construct(
        Template\Context $context,
        ReportCollectionFactory $reportCollectionFactory,
        ImageHelper $imageHelper,
        VendorSession $vendorSession, // Injecting Vnecoms Session
        array $data = []
    ) {
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->vendorSession = $vendorSession;
        parent::__construct($context, $data);
    }

    /**
     * Get Most Viewed Products for Logged-in Vendor Only
     */
    public function getMostViewedProducts()
    {
        // 1. Get Logged-in Vendor ID
        $vendorId = $this->vendorSession->getVendor()->getId();

        if (!$vendorId) {
            return false;
        }

        // 2. Create Report Collection
        $collection = $this->reportCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addViewsCount(); // Yeh function report_viewed_product_aggregated_daily table se data lata hai

        // 3. Filter by Vnecoms Vendor ID
        // Vnecoms products table mein 'vendor_id' column use karta hai
        $collection->addAttributeToFilter('vendor_id', $vendorId);

        $collection->setPageSize(10)
            ->setCurPage(1);
            
        return $collection;
    }

    public function getProductImage($product)
    {
        return $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }
}