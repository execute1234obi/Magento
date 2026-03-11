<?php
namespace Vendor\QuoteRequest\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Vnecoms\Vendors\Model\VendorFactory;
use Magento\Framework\App\ResourceConnection;

class View extends Template
{
    protected $catalogSession;
    protected $productRepository;
    protected $_countryFactory;
    protected $_directoryHelper;
    protected $vendorFactory;
    protected $resource;

    public function __construct(
        Template\Context $context,
        CatalogSession $catalogSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
         VendorFactory $vendorFactory,
         ResourceConnection $resource,
        array $data = []
    ) {
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->_countryFactory = $countryFactory;
        $this->_directoryHelper = $directoryHelper;
        $this->vendorFactory = $vendorFactory;
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    public function getCountryCollection() {
            return $this->_countryFactory->create()->loadByStore();
        }

        public function getRegionJson() {
            return $this->_directoryHelper->getRegionJson();
        }
    public function getQuoteItems()
    {
        
       $quoteCart = $this->catalogSession->getQuoteItems() ?? [];
        $items = [];
        //echo "<pre>"; 
        //print_r($quoteCart); 
        //echo "</pre>";
        //die("Session Data Check"); // Page yahi ruk jayega aur data dikhayega
        foreach ($quoteCart as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                $items[] = [
                    'product' => $product,
                    'qty' => 1
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $items;
    }
    public function getVendorName($product)
{
    $vendorId = $product->getData('vendor_id');

    if (!$vendorId) {
        return 'Admin';
    }

    try {
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $vendor->getName();
    } catch (\Exception $e) {
        return 'Vendor';
    }
}
public function getVendorCompanyName($vendorId)
{
    $connection = $this->resource->getConnection();

    $table = $this->resource->getTableName('ves_vendor_entity_varchar');

    $select = $connection->select()
        ->from($table, ['value'])
        ->where('entity_id = ?', $vendorId)
        ->where('attribute_id = ?', 174)
        ->limit(1);

    return $connection->fetchOne($select);
}
public function getVendorLogo($vendorId)
{
    $connection = $this->resource->getConnection();

    $table = $this->resource->getTableName('ves_vendor_entity_varchar');

    $select = $connection->select()
        ->from($table, ['value'])
        ->where('entity_id = ?', $vendorId)
        ->where('attribute_id = ?', 187)
        ->limit(1);

    $logo = $connection->fetchOne($select);

    if ($logo) {
         $mediaUrl = $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return rtrim($mediaUrl,'/') . '/' . ltrim($logo,'/');
       //return $this->getUrl('media') . $logo;
    }

    return '';
}
}
