<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Vendor\VendorsVerification\Helper\Data as VerificationHelper;

class NewAction extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * ACL resource
     */
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    /**
     * @var VendorSession
     */
    protected $_vendorSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var VerificationHelper
     */
    protected $helper;

    /**
     * Constructor
     */
    public function __construct(
        Context $context,
        VendorSession $vendorSession,
        Registry $registry,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository, // kept for future use
        VerificationHelper $helper,
        StoreManagerInterface $storeManager,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->_vendorSession     = $vendorSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeRepository   = $storeRepository;
        $this->storeManager      = $storeManager;
        $this->coreRegistry      = $registry;
        $this->helper            = $helper;

        parent::__construct($context);
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $vendor = $this->_vendorSession->getVendor();

        if (!$vendor || !$vendor->getId()) {
            $this->messageManager->addErrorMessage(__('Invalid vendor session.'));
            return $this->_redirect('vendors/account/login');
        }

        $vendorId = $vendor->getId();

        if ($this->helper->isAlreadyApplied($vendorId)) {
            $this->messageManager->addErrorMessage(
                __("You have already applied for verification.")
            );
            return $this->_redirect('vendorverification/verification/index/');
        }

        $this->_initAction();
        $title = $this->_view->getPage()->getConfig()->getTitle();

        $this->setActiveMenu('Vnecoms_Vendors::vendorverification_manage');
        $title->prepend(__('Vendor Verification'));
        $title->prepend(__('Verification'));

        $this->_addBreadcrumb(__('Verification'), __('Verification'))
            ->_addBreadcrumb(__('Manage Verification'), __('Request Verification'));

        $this->_view->renderLayout();
    }
}
