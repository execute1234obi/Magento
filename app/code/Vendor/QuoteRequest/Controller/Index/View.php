<?php
namespace Vendor\QuoteRequest\Block;

use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

class View extends Template
{
    protected $quoteCollectionFactory;

    public function __construct(
        Template\Context $context,
        QuoteCollectionFactory $quoteCollectionFactory,
        array $data = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getQuotes()
    {
        return $this->quoteCollectionFactory->create();
    }
}
