<?php
namespace Business\VendorVisitorReport\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface as Logger;

class ReportProfilevisitorPredispatch implements ObserverInterface
{
	  /**
	 * @var \Vnecoms\VendorsGroup\Helper\Data
	 */
    protected $_groupHelper;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;   
    
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    protected $logger;
    
    /**
     * Constructor
     *
     * @param \Business\VendorVisitorReport\Helper\Data $groupHelper
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect     
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Business\VendorVisitorReport\Helper\Data $groupHelper,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,        
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Logger $logger
    ) {
        $this->_groupHelper = $groupHelper;
        $this->_vendorSession = $vendorSession;
        $this->_redirect = $redirect;        
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }
    
    /**
     *
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $groupId = $this->_vendorSession->getVendor()->getGroupId();
        /*if (!$this->_groupHelper->canViewProfileVisitorReport($groupId)) {        
            $controllerAction = $observer->getControllerAction();
            $this->messageManager->addError(__("You are not allowed to do this action."));
            $this->_redirect->redirect($controllerAction->getResponse(), 'vendors');
            $controllerAction->getActionFlag()->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH, true);
            return;
        }*/
        
        
    }
}


