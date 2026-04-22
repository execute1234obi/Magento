<?php
namespace Vendor\VendorMessagesSubMenu\Model\ResourceModel\Message\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

class InboxCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected $vendorSession;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Vnecoms\Vendors\Model\Session $vendorSession
    ) {
        $this->vendorSession = $vendorSession;

        $mainTable = 'ves_vendor_message';
        $resourceModel = 'Vnecoms\VendorsMessage\Model\ResourceModel\Message';

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    protected function _construct()
    {
        parent::_construct();

        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('created_at', 'msg_detail.created_at');
        $this->addFilterToMap('message_id', 'main_table.message_id');
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // Default filters
        $this->addFieldToFilter('is_deleted', 0);
        $this->addFieldToFilter('is_inbox', 1);
$vendor = $this->vendorSession->getVendor();
$vendorId = $vendor->getId();

// get resource connection
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();

$tableName = $resource->getTableName('ves_vendor_user');

// fetch customer_id
$customerId = $connection->fetchOne(
    "SELECT customer_id FROM {$tableName} WHERE vendor_id = " . (int)$vendorId
);

// DEBUG (optional)
//echo "Vendor ID: " . $vendorId . "<br>";
//echo "Customer ID: " . $customerId . "<br>";

// apply filter
$this->getSelect()->where(
    '(main_table.owner_id = ? OR msg_detail.receiver_id = ? OR main_table.owner_id = 0)',
    $customerId
);
        // Join message details
        $this->getSelect()->joinLeft(
            ['msg_detail' => $this->getTable('ves_vendor_message_detail')],
            'main_table.message_id = msg_detail.message_id',
            ['*', 'msg_count' => 'count(msg_detail.detail_id)']
        );

        $this->getSelect()->group('msg_detail.message_id');
        $this->_joinedTables['msg_detail'] = true;
        $this->setOrder('msg_detail.created_at', self::SORT_ORDER_DESC);
   //     echo $this->getSelect()->__toString();
//exit;
    }
}