<?php
namespace CustomVendor\HideVnecomsMenu\Controller\Report;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action; // Crucial: This class defines _isAllowed()
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
        parent::__construct($context); // Crucial: Calls the parent constructor where _isAllowed() is set up
    }

    /**
     * The main action method for this controller.
     * This is where the logic for your "Profile Visitors" page will go.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vnecoms_VendorsReport::report'); // Highlights the "Reports" menu item
        $resultPage->getConfig()->getTitle()->prepend(__('Profile Visitors Report')); // Sets the page title

        // You'll likely render a PHTML template here for your report's content.
        // For example:
        // $resultPage->addContent($resultPage->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class)->setTemplate('CustomVendor_HideVnecomsMenu::report/profile_visitors.phtml'));

        return $resultPage;
    }

    /**
     * Check if the user is allowed to access this action.
     * This method is inherited from Magento\Backend\App\Action.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CustomVendor_HideVnecomsMenu::profile_visitors_resource');
    }
}
