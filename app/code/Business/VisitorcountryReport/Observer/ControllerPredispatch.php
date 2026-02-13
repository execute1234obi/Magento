<?php
namespace Business\VisitorcountryReport\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Business\VisitorcountryReport\Model\Event;
use Psr\Log\LoggerInterface;
use Business\VendorVisitorReport\Helper\Ip2Location;

class ControllerPredispatch implements ObserverInterface
{
    protected $_storeManager;
    protected $_visitedIndxFactory;
    protected $_visitedIndexCollectionFactory;
    protected $_visitorreportCountryFactory;
    protected $_customerSession;
    protected $_customerVisitor;
    protected $eventSaver;
    protected $_localeDate;
    protected $dateTime;
    protected $logger;
    protected $ip2Location;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Business\VisitorcountryReport\Model\VisitedFactory $visitedIndxFactory,
        \Business\VisitorcountryReport\Model\ResourceModel\Visited\CollectionFactory $visitedIndexCollectionFactory,
        \Business\VisitorcountryReport\Model\CountryFactory $visitorreportCountryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        EventSaver $eventSaver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        LoggerInterface $logger,
         Ip2Location $ip2Location
    ) {
        $this->_storeManager = $storeManager;
        $this->_visitedIndxFactory = $visitedIndxFactory;
        $this->_visitedIndexCollectionFactory = $visitedIndexCollectionFactory;
        $this->_visitorreportCountryFactory = $visitorreportCountryFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->eventSaver = $eventSaver;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
         $this->ip2Location = $ip2Location; 
    }

    public function execute(Observer $observer)
{
    try {
        $this->logger->info('--- VisitorcountryReport START ---');

        $visitorIp = $this->ip2Location->getIp();
        $visitorCountry = $this->ip2Location->getLocationCountry();

        if (!$visitorCountry) {
            $this->logger->info('Exiting: Country not detected');
            return;
        }

        // Visitor / Customer पहचान
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = (int)$this->_customerSession->getCustomerId();
            $visitorId = null;
        } else {
            $visitorId = (int)$this->_customerVisitor->getId();
            $customerId = null;

            if (!$visitorId) {
                $this->logger->info('Exiting: No Visitor ID');
                return;
            }
        }

        $storeId = (int)$this->_storeManager->getStore()->getId();
        $today = $this->dateTime->gmDate(
            'Y-m-d',
            $this->_localeDate->date()->getTimestamp()
        );

        // ✅ Duplicate control (same day + same user + same country)
        $collection = $this->_visitedIndexCollectionFactory->create();
        $collection->addFieldToFilter('visitor_country', $visitorCountry)
                   ->addFieldToFilter('added_at', $today);

        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        } else {
            $collection->addFieldToFilter('visitor_id', $visitorId);
        }

        if ($collection->getSize() > 0) {
            $this->logger->info('Duplicate visit रोक दिया');
            return;
        }

        // ✅ Save visited index
        $visitedData = [
            'store_id'        => $storeId,
            'visitor_country' => $visitorCountry,
            'visitor_ip'      => $visitorIp,
            'added_at'        => $today,
            'customer_id'     => $customerId,
            'visitor_id'      => $visitorId
        ];

        $this->_visitedIndxFactory->create()
            ->setData($visitedData)
            ->save();

        $this->logger->info('Visited index saved');

        // ✅ Country mapping load
        $countryModel = $this->_visitorreportCountryFactory
            ->create()
            ->load($visitorCountry, 'country_id');

        $countryId = (int)$countryModel->getId();

        $this->logger->info('Mapped Country ID: ' . $countryId);

        if (!$countryId) {
            $this->logger->info('Country mapping missing');
            return;
        }

        // ✅ Save report_event
        $this->eventSaver->save(
            7, // EVENT_VISITOR_VISIT_COUNTRY_LOG
            $countryId
        );

        $this->logger->info('Report event saved');
        $this->logger->info('--- VisitorcountryReport END ---');

    } catch (\Exception $e) {
        $this->logger->critical(
            'VisitorcountryReport Error: ' . $e->getMessage()
        );
    }
}

}
