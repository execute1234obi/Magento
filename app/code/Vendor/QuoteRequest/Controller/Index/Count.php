<?php

namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;

class Count extends Action
{
    protected $session;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        Session $session,
        JsonFactory $jsonFactory
    ) {
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $items = $this->session->getQuoteItems() ?: [];

        return $this->jsonFactory->create()->setData([
            'count' => count($items)
        ]);
    }
}