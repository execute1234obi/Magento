<?php
namespace Business\VendorVisitorReport\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Session as CustomerSession;
use Business\VendorVisitorReport\Helper\Ip2Location;
use Psr\Log\LoggerInterface;


class SaveProfileVisitor implements ObserverInterface
{
    protected $resource;
    protected $customerSession;
    protected $ip2Location;
    protected $logger;
    public function __construct(
        ResourceConnection $resource,
        CustomerSession $customerSession,
        Ip2Location $ip2Location,
         LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->ip2Location = $ip2Location;
         $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
    if (!$observer->getControllerAction()) {
            return;
        }

    $controller = $observer->getControllerAction();
    $request = $controller->getRequest();

    if ($request->getFullActionName() !== 'vendorspage_index_index') {
        return;
    }

    $vendorCode = (string) $request->getParam('vendor_id');
    $vendorCode = trim($vendorCode);

    if ($vendorCode === '') {
        return;
    }

    $connection = $this->resource->getConnection();

    $vendorId = $connection->fetchOne(
        $connection->select()
            ->from(
                $this->resource->getTableName('ves_vendor_entity'),
                ['entity_id']
            )
            ->where('LOWER(vendor_id) = ?', strtolower($vendorCode))
            ->limit(1)
    );

    if (!$vendorId) {
        return;
    }


        $connection = $this->resource->getConnection();
        $table = $connection->getTableName('business_vendor_profile_visitor');

        $customerId = $this->customerSession->isLoggedIn()
            ? (int) $this->customerSession->getCustomerId()
            : null;

        $visitorIp = $this->ip2Location->getIp();
        $countryCode = $this->ip2Location->getLocationCountry();
       // $this->logger->info("Vendor: $vendorId, IP: $visitorIp, Country: $countryCode, Customer: $customerId");

        // 🚫 Duplicate control (same vendor + IP + same day)
        $select = $connection->select()
            ->from($table, ['entity_id'])
            ->where('vendor_id = ?', $vendorId)
            ->where('visitor_ip = ?', $visitorIp)
            ->where('DATE(visited_at) = CURDATE()');

        if ($connection->fetchOne($select)) {
            return;
        }

        $connection->insert($table, [
            'vendor_id'   => $vendorId,
            'customer_id' => $customerId,
            'visitor_ip'  => $visitorIp,
            'country_code'=> $countryCode,
            'visited_at'  => date('Y-m-d H:i:s')
        ]);
        $this->logger->info("Visitor saved for vendor $vendorId with IP $visitorIp");


    }
}
