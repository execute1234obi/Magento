<?php
namespace Business\VisitorcountryReport\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Business\VisitorcountryReport\Model\Event;
use Psr\Log\LoggerInterface;


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
          
    }

    /**
     * controller_action_predispatch observer
     */
    // public function execute(Observer $observer)
    // {
    //     try {
    //         $this->logger->info('VisitorcountryReport PreDispatch Fired');

    //         /**
    //          * TEMP fallback country
    //          * (replace later with IP logic)
    //          */
    //         $visitorCountry = 'IN';

    //         if (!$visitorCountry) {
    //             return;
    //         }

    //         /**
    //          * IMPORTANT:
    //          * visitor id must exist for guest
    //          */
    //         if (!$this->_customerSession->isLoggedIn()) {
    //             $visitorId = (int)$this->_customerVisitor->getId();
    //             if (!$visitorId) {
    //                 return;
    //             }
    //         }

    //         $visitedData = [];
    //         $visitedData['store_id'] = (int)$this->_storeManager->getStore()->getId();
    //         $visitedData['visitor_country'] = $visitorCountry;
    //         $visitedData['added_at'] = $this->dateTime->gmDate(
    //             'Y-m-d',
    //             $this->_localeDate->date()->getTimestamp()
    //         );

    //         $collection = $this->_visitedIndexCollectionFactory->create();
    //         $collection->addFieldToFilter('visitor_country', $visitedData['visitor_country'])
    //                    ->addFieldToFilter('added_at', $visitedData['added_at']);

    //         if ($this->_customerSession->isLoggedIn()) {
    //             $visitedData['customer_id'] = (int)$this->_customerSession->getCustomerId();
    //             $collection->addFieldToFilter('customer_id', $visitedData['customer_id']);
    //         } else {
    //             $visitedData['visitor_id'] = (int)$this->_customerVisitor->getId();
    //             $collection->addFieldToFilter('visitor_id', $visitedData['visitor_id']);
    //         }

    //         /**
    //          * Insert only once per day
    //          */
    //         if ($collection->getSize() > 0) {
    //             return;
    //         }

    //         // save visited index
    //         $this->_visitedIndxFactory->create()->setData($visitedData)->save();

    //         /**
    //          * Load country record
    //          */
    //         $countryModel = $this->_visitorreportCountryFactory
    //             ->create()
    //             ->load($visitorCountry, 'country_id');

    //         $countryId = (int)$countryModel->getId();
    //         if (!$countryId) {
    //             return;
    //         }

    //         /**
    //          * SAVE REPORT EVENT
    //          * event_type_id = 7 (already registered)
    //          */
    //         $this->eventSaver->save(
    //             Event::EVENT_VISITOR_VISIT_COUNTRY_LOG, // 7
    //             $countryId
    //         );

    //     } catch (\Exception $e) {
    //         $this->logger->critical(
    //             'VisitorcountryReport Error: ' . $e->getMessage()
    //         );
    //     }
    // }
    public function execute(Observer $observer)
{
    try {
        $this->logger->info('--- VisitorcountryReport START ---');

        $visitorCountry = 'IN';

        // 1. Check if logged in or has visitor ID
        if (!$this->_customerSession->isLoggedIn()) {
            $visitorId = (int)$this->_customerVisitor->getId();
            $this->logger->info('Visitor ID found: ' . $visitorId);
            if (!$visitorId) {
                $this->logger->info('Exiting: No Visitor ID');
                return;
            }
        }

        // 2. Load country record
        $countryModel = $this->_visitorreportCountryFactory->create()
            ->load($visitorCountry, 'country_id');
        
        $countryId = (int)$countryModel->getId();
        $this->logger->info('Mapped Country ID: ' . $countryId);

        if (!$countryId) {
            $this->logger->info('Exiting: Country ID 106 not found for IN');
            return;
        }

        // 3. Save to report_event (Directly calling EventSaver)
        $this->logger->info('Attempting to save to report_event...');
        $this->eventSaver->save(
            7, // Hardcoded for testing
            $countryId
        );
        
        $this->logger->info('--- VisitorcountryReport END ---');

    } catch (\Exception $e) {
        $this->logger->critical('Error: ' . $e->getMessage());
    }
}
}
