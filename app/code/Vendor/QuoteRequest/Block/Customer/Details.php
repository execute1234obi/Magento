<?php
namespace Vendor\QuoteRequest\Block\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vendor\QuoteRequest\Model\ResourceModel\QuoteItem\CollectionFactory as ItemCollectionFactory;

class Details extends Template
{
    protected $customerSession;
    protected $resource;
    protected $productRepository;
    protected $imageHelper;
    protected $priceHelper;
    protected $quoteFactory;
    protected $itemCollectionFactory;
    protected $formKey;

    private $quote;
    private $items;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        PriceHelper $priceHelper,
        QuoteFactory $quoteFactory,
        ItemCollectionFactory $itemCollectionFactory,
        FormKey $formKey,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->quoteFactory = $quoteFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getCustomerId(): int
    {
        return (int) $this->customerSession->getCustomerId();
    }

    public function getQuoteId(): int
    {
        return (int) $this->getRequest()->getParam('quote_id', $this->getRequest()->getParam('id'));
    }

    public function getQuote()
    {
        if ($this->quote !== null) {
            return $this->quote;
        }

        $quoteId = $this->getQuoteId();
        if ($quoteId <= 0 || $this->getCustomerId() <= 0) {
            $this->quote = null;
            return null;
        }

        $quote = $this->quoteFactory->create()->load($quoteId);
        if (!$quote->getId() || (int) $quote->getCustomerId() !== $this->getCustomerId()) {
            $this->quote = null;
            return null;
        }

        $this->quote = $quote;
        return $this->quote;
    }

    public function getItems()
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $quote = $this->getQuote();
        if (!$quote || !$quote->getId()) {
            $this->items = [];
            return $this->items;
        }

        $this->items = $this->itemCollectionFactory->create()
            ->addFieldToFilter('quote_id', (int) $quote->getId())
            ->setOrder('item_id', 'ASC');

        return $this->items;
    }

    public function getItemCount(): int
    {
        $items = $this->getItems();
        return is_array($items) ? count($items) : (int) $items->getSize();
    }

    public function getStatus(): string
    {
        $quote = $this->getQuote();
        $status = $quote ? strtolower(trim((string) $quote->getStatus())) : 'pending';
        return $status !== '' ? $status : 'pending';
    }

    public function getStatusLabel(): string
    {
        return ucfirst($this->getStatus());
    }

    public function getStatusClass(): string
    {
        return in_array($this->getStatus(), ['pending', 'approved', 'rejected', 'cancelled'], true)
            ? $this->getStatus()
            : 'default';
    }

    public function getRequestIdLabel(): string
    {
        return sprintf('#RFQ-%s', $this->getQuoteId());
    }

    public function getRequestDate(): string
    {
        $quote = $this->getQuote();
        $createdAt = $quote ? (string) $quote->getCreatedAt() : '';
        $timestamp = $createdAt !== '' ? strtotime($createdAt) : false;

        return $timestamp ? strtoupper(date('d M Y', $timestamp)) : '-';
    }

    public function getRequestTime(): string
    {
        $quote = $this->getQuote();
        $createdAt = $quote ? (string) $quote->getCreatedAt() : '';
        $timestamp = $createdAt !== '' ? strtotime($createdAt) : false;

        return $timestamp ? date('h:i A', $timestamp) : '-';
    }

    public function getVendorCompanyName($vendorId): string
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

    public function getSupplierName(): string
    {
        $quote = $this->getQuote();
        if (!$quote || !$quote->getId()) {
            return '';
        }

        $vendorId = (int) $quote->getVendorId();
        $supplier = $this->getVendorCompanyName($vendorId);

        return $supplier !== '' ? $supplier : (string) __('N/A');
    }

    public function getProduct($productId)
    {
        try {
            return $this->productRepository->getById((int) $productId);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductName($productId): string
    {
        $product = $this->getProduct($productId);
        return $product && $product->getId() ? (string) $product->getName() : (string) __('Product unavailable');
    }

    public function getProductSku($productId): string
    {
        $product = $this->getProduct($productId);
        return $product && $product->getId() ? (string) $product->getSku() : (string) $productId;
    }

    public function getProductImageUrl($productId): string
    {
        $product = $this->getProduct($productId);
        if (!$product || !$product->getId()) {
            return '';
        }

        try {
            return (string) $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function formatPrice($price): string
    {
        return ($price === null || $price === '')
            ? '-'
            : (string) $this->priceHelper->currency($price, true, false);
    }

    public function getMessage(): string
    {
        $quote = $this->getQuote();
        return $quote ? trim((string) $quote->getCustomerNote()) : '';
    }

    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    public function getReRequestUrl(): string
    {
        return $this->getUrl('quoterequest/customer/rerequest', ['quote_id' => $this->getQuoteId()]);
    }

    public function getCancelUrl(): string
    {
        return $this->getUrl('quoterequest/customer/cancel');
    }

    public function getHistoryUrl(): string
    {
        return $this->getUrl('quoterequest/customer/history');
    }

    public function getNewRequestUrl(): string
    {
        return $this->getUrl('quoterequest/view/index');
    }

    public function canCancel(): bool
    {
        return $this->getStatus() === 'pending';
    }
}
