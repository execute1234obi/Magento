<?php
namespace Vendor\QuoteRequest\Block\Adminhtml\Quote;

use Magento\Backend\Block\Template;
use Vendor\QuoteRequest\Model\QuoteFactory;
use Vendor\QuoteRequest\Model\ResourceModel\QuoteItem\CollectionFactory as ItemCollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;

class View extends Template
{
    protected $request;
    protected $quoteFactory;
    protected $itemCollectionFactory;
    protected $customerFactory;
    protected $resource;
    protected $productFactory;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        QuoteFactory $quoteFactory,
        ItemCollectionFactory $itemCollectionFactory,
        CustomerFactory $customerFactory,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->quoteFactory = $quoteFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    public function getQuote()
    {
        $quoteId = $this->request->getParam('quote_id');
        return $this->quoteFactory->create()->load($quoteId);
    }

    public function getItems()
    {
        $quoteId = $this->request->getParam('quote_id');
        return $this->itemCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId);
    }

    public function getCustomerName()
    {
        $quote = $this->getQuote();
        if (!$quote->getCustomerId()) {
            return 'Guest';
        }

        $customer = $this->customerFactory->create()->load($quote->getCustomerId());
        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }

    public function getVendorName()
{
    $quote = $this->getQuote();
    $vendorId = $quote->getVendorId();

    if (!$vendorId) {
        return 'N/A';
    }

    $connection = $this->resource->getConnection();
    $table = $this->resource->getTableName('ves_vendor_entity_varchar');

    $select = $connection->select()
        ->from($table, ['value'])
        ->where('entity_id = ?', $vendorId)
        ->where('attribute_id = ?', 174) // company attribute
        ->limit(1);

    return $connection->fetchOne($select) ?: 'N/A';
}
public function getProductName($productId)
{
    if (!$productId) {
        return 'N/A';
    }

    try {
        return $this->productFactory->create()
            ->load($productId)
            ->getName();
    } catch (\Exception $e) {
        return 'N/A';
    }
}
protected function _prepareLayout()
{
    parent::_prepareLayout();
    if ($this->getItems()) {
        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'vendor.quote.pager'
        )->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
         ->setCollection($this->getItems()); // Aapki collection yahan pass hogi
        $this->setChild('pager', $pager);
        $this->getItems()->load();
    }
    return $this;
}

public function getPagerHtml()
{
    return $this->getChildHtml('pager');
}
}