<?php
namespace CustomVendor\HideVnecomsMenu\Controller\Review;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * The main action method for this controller.
     * This will display the "Manage Reviews" page for vendors.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
       public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        // No setActiveMenu here if it's a new top-level.
        // You might use it for a sub-menu later if you want to highlight it.
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Reviews')); // Sets the page title in the browser tab

        // You'll add your review management logic and template rendering here later.
        // For now, this is enough to get the page working.

        return $resultPage;
    }

    /**
     * Check if the user is allowed to access this action.
     * This method uses the ACL resource defined for the new Reviews menu.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        // This resource ID must match the one in menu.xml and vacl.xml
        return $this->_authorization->isAllowed('CustomVendor_HideVnecomsMenu::reviews_top_level');
    }
}
