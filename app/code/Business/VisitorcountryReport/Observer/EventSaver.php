<?php
namespace Business\VisitorcountryReport\Observer;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Reports\Model\EventFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Psr\Log\LoggerInterface;

class EventSaver
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Visitor
     */
    protected $customerVisitor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        StoreManagerInterface $storeManager,
        EventFactory $eventFactory,
        Session $customerSession,
        Visitor $customerVisitor,
        LoggerInterface $logger
    ) {
        $this->storeManager    = $storeManager;
        $this->eventFactory    = $eventFactory;
        $this->customerSession = $customerSession;
        $this->customerVisitor = $customerVisitor;
        $this->logger          = $logger;
    }

    /**
     * Save report event
     *
     * @param int $eventTypeId
     * @param int $objectId
     * @return void
     */
    // public function save(int $eventTypeId, int $objectId): void
    // {
    //     try {
    //         // object_id must not be 0
    //         if (!$objectId) {
    //             return;
    //         }

    //         $storeId = (int)$this->storeManager->getStore()->getId();

    //         // SUBJECT (customer / visitor)
    //         if ($this->customerSession->isLoggedIn()) {
    //             $subjectId = (int)$this->customerSession->getCustomerId();
    //             $subtype   = 1; // CUSTOMER
    //         } else {
    //             $visitorId = (int)$this->customerVisitor->getId();
    //             if (!$visitorId) {
    //                 return; // visitor not initialized yet
    //             }
    //             $subjectId = $visitorId;
    //             $subtype   = 0; // VISITOR
    //         }

    //         /** @var \Magento\Reports\Model\Event $eventModel */
    //         $eventModel = $this->eventFactory->create();
    //         $eventModel->setData([
    //             'event_type_id' => $eventTypeId, // 7
    //             'object_id'     => $objectId,
    //             'subject_id'    => $subjectId,
    //             'subtype'       => $subtype,
    //             'store_id'      => $storeId,
    //             'logged_at'     => date('Y-m-d H:i:s'),
    //         ]);

    //         // Magento 2 correct save
    //         $eventModel->getResource()->save($eventModel);
           

    //     } catch (\Exception $e) {
    //         $this->logger->critical(
    //             'VisitorCountry Report Event Error: ' . $e->getMessage()
    //         );
    //     }
    // }
    public function save(int $eventTypeId, int $objectId): void
{
    try {
        $this->logger->info('Inside EventSaver::save for Object: ' . $objectId);
        $storeId = (int)$this->storeManager->getStore()->getId();
        
        // Debug Log
        $this->logger->info("Saving Event: Type $eventTypeId, Object $objectId, Store $storeId");

        $eventModel = $this->eventFactory->create();
        $eventModel->setData([
            'event_type_id' => $eventTypeId,
            'object_id'     => $objectId,
            'subject_id'    => 0, // Testing ke liye 0 rakhein
            'subtype'       => 0,
            'store_id'      => $storeId,
            'logged_at'     => date('Y-m-d H:i:s'),
        ]);

        $eventModel->getResource()->save($eventModel);
        $this->logger->info("Event Saved Successfully!");

    } catch (\Exception $e) {
        $this->logger->critical('Event Save Error: ' . $e->getMessage());
    }
}
}
