<?php

namespace Business\MostviewedVendors\Observer;

use Magento\Framework\Event\ObserverInterface;
use Business\MostviewedVendors\Model\Event;

class VendorProfileViewObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Business\MostviewedVendors\Model\Vendor\Index\ViewedFactory
     */    
    protected $_vendorIndxFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @var EventSaver
     */
    protected $eventSaver;

    /**
     * @var \Magento\Reports\Model\ReportStatus
     */
    private $reportStatus;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Business\VisitorcountryReport\Model\CountryFactory
     */
    protected $_visitorreportCountryFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Business\MostviewedVendors\Model\Vendor\Index\ViewedFactory $vendorIndxFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver,
        \Magento\Reports\Model\ReportStatus $reportStatus,
        \Magento\Framework\Registry $coreRegistry,
        \Business\VisitorcountryReport\Model\CountryFactory $visitorreportCountryFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->_vendorIndxFactory = $vendorIndxFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
        $this->reportStatus = $reportStatus;
        $this->_coreRegistry = $coreRegistry;
        $this->_visitorreportCountryFactory = $visitorreportCountryFactory;
        $this->logger = $logger;
    }

    /**
     * View Vendor Profile action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_customerSession->getVendorPageRouterMatchCount() == 1) {
            $vendorId = $observer->getEvent()->getCondition()->getVendor()->getId();
            $this->logger->info("Vendor Profile view Observer VendorID=" . $vendorId);

            $viewData = [
                'vendor_id' => $vendorId,
                'store_id'  => $this->_storeManager->getStore()->getId()
            ];

            if ($this->_customerSession->isLoggedIn()) {
                $viewData['customer_id'] = $this->_customerSession->getCustomerId();
            } else {
                $viewData['visitor_id'] = $this->_customerVisitor->getId() ?: 0;
            }

            // Get Visitor Country Id (Advertisement module removed)
            $countryId = 0;
            $visitorCountryObj = $this->_visitorreportCountryFactory->create()->load(
                '', // No Advertisement module logic
                'country_id'
            );
            $countryId = $visitorCountryObj->getData('id') ?: 0;

            $this->logger->info("Vendor Profile view Country ID=" . $countryId);
            $this->logger->info("Vendor Profile view viewData=", $viewData);

            $this->_vendorIndxFactory->create()->setData($viewData)->save()->calculate();
            $this->eventSaver->save(Event::EVENT_VES_VENDOR_PROFILE_VIEW, $vendorId, null, $countryId);
        }
    }
}
