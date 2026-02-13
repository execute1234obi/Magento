<?php

namespace Business\MostviewedVendors\Observer;

use Magento\Framework\Event\ObserverInterface;
//use Magento\Reports\Model\Event;
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
    
    protected $cookieVisitorCountry;
    
    protected $_visitorreportCountryFactory;
    
    protected $helperIp2Location;
    
    protected $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Business\MostviewedVendors\Model\Vendor\Index\ViewedFactory $vendorIndxFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param EventSaver $eventSaver
     * @param \Magento\Reports\Model\ReportStatus $reportStatus
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
        \Business\Advertisement\Cookie\VisitorCountry $visitorCountry,                
        \Business\Advertisement\Helper\Ip2Location $helperIp2Location,
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
        $this->cookieVisitorCountry = $visitorCountry;
        $this->helperIp2Location = $helperIp2Location;
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
		
	   if($this->_customerSession->getVendorPageRouterMatchCount() == 1) {
            $vendorId = $observer->getEvent()->getCondition()->getVendor()->getId();        
            $this->logger->info("vendor Profile view Observer VendorID=".$vendorId);
            
            $viewData['vendor_id'] = $vendorId;
            $viewData['store_id']   = $this->_storeManager->getStore()->getId();
            if ($this->_customerSession->isLoggedIn()) {
                $viewData['customer_id'] = $this->_customerSession->getCustomerId();
            } else {
                $viewData['visitor_id'] = $this->_customerVisitor->getId();
                $viewData['visitor_id'] = !isset($viewData['visitor_id']) ? 0: $viewData['visitor_id'];
            }        
            //$this->logger->info("vendor Profile view Observer viewData=",$viewData);
            //Get Visitor Country Id
            $countryId = 0;
            $visitorCountry =  $this->cookieVisitorCountry->get();           
		    if($visitorCountry == ''){
			   //Call API and get Country Code
			   $visitorCountry  = $this->helperIp2Location->getLocationCountry();			
		     }
		    if($visitorCountry!=''){
             $visitorCountryObj = $this->_visitorreportCountryFactory->create()->load($visitorCountry,'country_id');             
             $countryId = $visitorCountryObj->getData('id');            
		     }
		     $this->logger->info("vendor Profile view Country ID=".$countryId);
		     $this->logger->info("vendor Profile view vendor ID=".$vendorId);
		     $this->logger->info("vendor Profile view viewData=",$viewData);
            $this->_vendorIndxFactory->create()->setData($viewData)->save()->calculate();
            $this->eventSaver->save(Event::EVENT_VES_VENDOR_PROFILE_VIEW, $vendorId, null,$countryId);        
        
	   }
    }
}
