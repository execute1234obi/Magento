<?php
namespace Vendor\QuoteRequest\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    // public function execute()
    // {
    //     die(implode(', ', $this->_view->getLayout()->getUpdate()->getHandles()));
    //     $quoteId = $this->getRequest()->getParam('quote_id');

    //     if (!$quoteId) {
    //         $this->messageManager->addErrorMessage(__('Quote ID missing.'));
    //         return $this->_redirect('*/*/');
    //     }

    //     $resultPage = $this->resultPageFactory->create();
    //     $resultPage->getConfig()->getTitle()->prepend(__('View Quote #' . $quoteId));

    //     return $resultPage;
    // }

  public function execute()
{
    return $this->resultFactory->create(
        \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
    );
}

}