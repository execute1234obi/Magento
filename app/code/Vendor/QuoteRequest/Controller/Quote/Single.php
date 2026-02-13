<?php
namespace Vendor\QuoteRequest\Controller\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Single extends Action
{
    protected $quoteFactory;
    protected $quoteItemFactory;

    public function __construct(
        Context $context,
        \Vendor\QuoteRequest\Model\QuoteFactory $quoteFactory,
        \Vendor\QuoteRequest\Model\QuoteItemFactory $quoteItemFactory
    ) {
        parent::__construct($context);
        $this->quoteFactory = $quoteFactory;
        $this->quoteItemFactory = $quoteItemFactory;
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

            $item = $this->quoteItemFactory->create();
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
