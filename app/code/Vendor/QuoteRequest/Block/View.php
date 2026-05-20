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

        foreach ($quoteCart as $item) {
            $quoteItem = $this->normalizeQuoteItem($item);
            $displayProductId = $quoteItem['selected_product_id'] ?: $quoteItem['product_id'];

            if (!$displayProductId) {
                continue;
            }

            try {
                $product = $this->productRepository->getById($displayProductId);
                $baseProduct = $product;

                if ($quoteItem['product_id'] && $quoteItem['product_id'] !== $displayProductId) {
                    try {
                        $baseProduct = $this->productRepository->getById($quoteItem['product_id']);
                    } catch (\Exception $e) {
                        $baseProduct = $product;
                    }
                }

                $items[] = [
                    'product' => $product,
                    'base_product' => $baseProduct,
                    'product_id' => $quoteItem['product_id'] ?: $displayProductId,
                    'selected_product_id' => $quoteItem['selected_product_id'],
                    'qty' => max(1, (int) $quoteItem['qty']),
                    'variant_attributes' => $this->getVariantAttributes($product)
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $items;
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

    private function getVariantAttributes($product)
    {
        $variantAttributes = [];
        $attributes = [
            'color' => __('Color'),
            'size' => __('Size')
        ];

        foreach ($attributes as $code => $label) {
            $value = $product->getAttributeText($code);

            if (is_array($value)) {
                $value = implode(', ', array_filter(array_map('strval', $value)));
            } elseif (is_object($value)) {
                $value = method_exists($value, '__toString') ? (string) $value : '';
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $variantAttributes[] = [
                'code' => $code,
                'label' => $label,
                'value' => $value
            ];
        }

        return $variantAttributes;
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
