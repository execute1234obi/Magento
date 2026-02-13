<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;

class Create extends Action
{
    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');

        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Invalid product.'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        /** 👉 Save to your quote_request table here **/

        $this->messageManager->addSuccessMessage(__('Product added to Quote.'));

        return $this->resultRedirectFactory->create()
            ->setPath('quoterequest/index/view');
    }
}
