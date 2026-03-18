<?php 
namespace Vendor\QuoteRequest\Block\Rfq;

use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\CollectionFactory;
use Vendor\QuoteRequest\Model\ResourceModel\QuoteItem\CollectionFactory as ItemCollectionFactory;

class History extends \Magento\Framework\View\Element\Template
{
    protected $quoteCollectionFactory;
    protected $itemCollectionFactory;
    protected $_vendorSession;
    protected $_quoteCollection;

    public function __construct(
        Template\Context $context,
        CollectionFactory $quoteCollectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        array $data = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->itemCollectionFactory  = $itemCollectionFactory; 
        $this->_vendorSession = $vendorSession;
        parent::__construct($context, $data);
    }

 public function getQuoteHistory()
{
    if (!$this->_quoteCollection) {
        $vendorId = $this->_vendorSession->getVendor()->getId();
        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('main_table.vendor_id', $vendorId);
        
        // Manual Pagination Logic
        $pageSize = 5; 
        $currentPage = (int)$this->getRequest()->getParam('p', 1);
        
        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);

        $this->_quoteCollection = $collection;
    }
    return $this->_quoteCollection;
}

public function getPagerData()
{
    $collection = $this->getQuoteHistory();
    // Safe check: agar collection null ho (waise hoga nahi upar wale fix se)
    if (!$collection) return ['total_pages' => 0];

    $pageSize = $collection->getPageSize();
    $totalItems = $collection->getSize();
    $totalPages = ceil($totalItems / $pageSize);
    $currentPage = $collection->getCurPage();

    return [
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'has_next'     => ($currentPage < $totalPages),
        'has_prev'     => ($currentPage > 1),
        'total_records' => $totalItems
    ];
}

    public function getQuote()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->quoteCollectionFactory->create()
            ->addFieldToFilter('entity_id', $id)
            ->getFirstItem();
    }

    public function getItemsByQuoteId($quoteId)
    {
        return $this->itemCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId);
    }
}