<?php
namespace Vendor\QuoteRequest\Controller\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Single extends Action
{
    protected $quoteFactory;
    protected $itemFactory; // Naam change kiya

    public function __construct(
        Context $context,
        \Vendor\QuoteRequest\Model\QuoteFactory $quoteFactory,
        \Vendor\QuoteRequest\Model\ItemFactory $itemFactory // QuoteItemFactory ko ItemFactory kiya
    ) {
        parent::__construct($context);
        $this->quoteFactory = $quoteFactory;
        $this->itemFactory = $itemFactory; // Sync kiya
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        try {
            $quote = $this->quoteFactory->create();
            $quote->setData([
                'customer_id' => 1, // replace with session later
                'status' => 'pending'
            ]);
            $quote->save();

            $item = $this->itemFactory->create(); // ItemFactory use kiya
            $item->setData([
                'quote_id' => $quote->getId(),
                'product_id' => $data['product_id'],
                'qty' => $data['qty']
            ]);
            $item->save();

            $this->messageManager->addSuccessMessage(__('Quote request submitted'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}