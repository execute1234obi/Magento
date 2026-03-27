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

    // Testing Method
    // protected function _toHtml()
    // {
    //     return '<h1 style="color:green;">2. Block Class Loaded!</h1>' . parent::_toHtml();
    // }
    /**
     * URL se Quote ID lekar single quote return karega
     */
    public function getQuote()
    {
        $id = $this->getRequest()->getParam('id'); // Ensure URL has ?id=...
        $collection = $this->quoteCollectionFactory->create()
            ->addFieldToFilter('entity_id', $id);
        
        return $collection->getFirstItem();
    }
   public function getQuoteHistory()
{
    $vendorId = $this->_vendorSession->getVendor()->getId();

    $collection = $this->quoteCollectionFactory->create()
       ->addFieldToFilter('main_table.vendor_id', $vendorId);

    //echo "<pre>";
    //print_r($collection->getSelect()->__toString());
    //exit;

    return $collection;
}
    public function getItemsByQuoteId($quoteId)
    {
        return $this->itemCollectionFactory->create()
        ->addFieldToFilter('quote_id', $quoteId);
    }

}