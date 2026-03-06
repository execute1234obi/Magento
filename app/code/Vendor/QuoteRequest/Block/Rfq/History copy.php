<?php
namespace Vendor\QuoteRequest\Block\Vendor;

use Magento\Framework\View\Element\Template;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Customer\Model\Session;

class History extends Template
{
    protected $quoteCollectionFactory;
    protected $customerSession;

    public function __construct(
        Template\Context $context,
        CollectionFactory $quoteCollectionFactory,
        Session $customerSession,
        array $data = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getVendorQuotes()
    {
        $vendorId = $this->customerSession->getCustomerId();

        return $this->quoteCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setOrder('created_at', 'DESC');
    }
}
