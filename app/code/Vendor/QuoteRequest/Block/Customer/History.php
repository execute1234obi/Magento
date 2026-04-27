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
    protected $_rfqData = null; // Cache for current page data

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

    public function getCustomerId() {
        return (int) $this->customerSession->getCustomerId();
    }

    /**
     * Get Total Count for Pager
     */
    public function getRfqCount()
    {
        $connection = $this->resource->getConnection();
        $quoteTable  = $this->resource->getTableName('vendor_quote');

        $select = $connection->select()
            ->from(['q' => $quoteTable], [
                'count' => new \Zend_Db_Expr('COUNT(DISTINCT q.quote_id)')
            ])
            ->where('q.customer_id = ?', $this->getCustomerId());

        return (int) $connection->fetchOne($select);
    }

    public function getRfqs()
    {
        if ($this->_rfqData !== null) {
            return $this->_rfqData;
        }

        $page = max(1, (int) $this->getRequest()->getParam('p', 1));
        $limit = max(1, (int) $this->getRequest()->getParam('limit', 10));
        $offset = ($page - 1) * $limit;

        $connection = $this->resource->getConnection();
        $quoteTable  = $this->resource->getTableName('vendor_quote');
        $detailTable = $this->resource->getTableName('vendor_quote_item');

        $quoteSelect = $connection->select()
            ->from(['q' => $quoteTable])
            ->where('q.customer_id = ?', $this->getCustomerId())
            ->order('q.created_at DESC')
            ->limit($limit, $offset);

        $quotes = $connection->fetchAll($quoteSelect);
        if (!$quotes) {
            $this->_rfqData = [];
            return $this->_rfqData;
        }

        $quoteIds = array_map('intval', array_column($quotes, 'quote_id'));
        $itemsByQuote = [];

        if (!empty($quoteIds)) {
            $itemSelect = $connection->select()
                ->from(['d' => $detailTable], [
                    'item_id',
                    'quote_id',
                    'product_id',
                    'qty',
                    'proposed_price'
                ])
                ->where('d.quote_id IN (?)', $quoteIds)
                ->order(['d.quote_id ASC', 'd.item_id ASC']);

            foreach ($connection->fetchAll($itemSelect) as $itemRow) {
                $itemsByQuote[(int) $itemRow['quote_id']][] = $itemRow;
            }
        }

        $this->_rfqData = [];
        foreach ($quotes as $quote) {
            $quoteId = (int) ($quote['quote_id'] ?? 0);
            $quote['items'] = $itemsByQuote[$quoteId] ?? [];
            $quote['item_count'] = count($quote['items']);
            $this->_rfqData[] = $quote;
        }

        return $this->_rfqData;
    }

    public function getVendorCompanyName($vendorId)
    {
        $vendorId = (int) $vendorId;
        if ($vendorId <= 0) {
            return '';
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('ves_vendor_entity_varchar');

        $select = $connection->select()
            ->from($table, ['value'])
            ->where('entity_id = ?', $vendorId)
            ->where('attribute_id = ?', 174)
            ->limit(1);

        return (string) $connection->fetchOne($select);
    }

    /**
     * Manual Pager HTML for Arrays
     */
    public function getPagerHtml() {
        $totalCount = $this->getRfqCount();
        $limit = (int) $this->getRequest()->getParam('limit', 10);
        
        // Agar items limit se kam hain toh pager nahi dikhayenge
        if ($totalCount <= $limit) return '';

        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'rfq.history.pager'
        )->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
         ->setTotalNum($totalCount)
         ->setLimit($limit)
         ->setCollection(new \Magento\Framework\DataObject()); // Dummy object for Pager

        return $pager->toHtml();
    }

    public function getProduct($productId) {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) { return null; }
    }

    public function getImageUrl($product) {
        if (!$product || !$product->getId()) return null;
        return $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }

    public function formatPrice($price) {
        return ($price === null || $price === '') ? '-' : $this->priceHelper->currency($price, true, false);
    }
}
