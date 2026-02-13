<?php
namespace Custom\HsCode\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
        //  $resultPage = $this->resultPageFactory->create();

        // // Log the page layout to ensure it's being loaded
        // $this->_logger->debug('Layout loaded: ' . $resultPage->getConfig()->getTitle()->get() );

        // return $resultPage;
    }
}
