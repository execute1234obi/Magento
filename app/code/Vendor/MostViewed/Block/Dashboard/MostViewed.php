<?php
namespace Vendor\MostViewed\Block\Dashboard;

class MostViewed extends \Magento\Framework\View\Element\Template
{
    protected $_reportCollectionFactory;
    protected $_customerSession;
    protected $_imageHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_reportCollectionFactory = $reportCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
        //die("Block Class Loaded Successfully!");
    }

    public function getMostViewedProducts()
    {
        $vendorId = $this->_customerSession->getCustomerId();
        
        $collection = $this->_reportCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addViewsCount() 
            ->setPageSize(5);

        // Filter by Vendor ID (Make sure 'vendor_id' matches your attribute code)
        $collection->addAttributeToFilter('vendor_id', $vendorId); 
        // Debugging: Ye check karega ki total kitne products mile
    //echo "Total Products Found: " . $collection->getSize();
    
    // Debugging: Agar aap SQL query dekhna chahte hain
     //die($collection->getSelect()->__toString());
        return $collection;
    }

    public function getProductImage($product)
    {
        return $this->_imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }
}