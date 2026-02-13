<?php
namespace Vendor\VendorsVerification\Model\ResourceModel\VendorVerification\Admin\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager
    ) {
        $mainTable = 'business_vendor_verification';        
        $resourceModel = 'Vendor\VendorsVerification\Model\ResourceModel\VendorVerification';
        $this->_idFieldName = 'verification_id';
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

  protected function _initSelect()
{
    parent::_initSelect();

    // 1. Join for Seller Name (Varchar Attribute - ID 183)
    $this->getSelect()->joinLeft(
        ['vendor_name_attr' => $this->getTable('ves_vendor_entity_varchar')],
        'main_table.vendor_id = vendor_name_attr.entity_id AND vendor_name_attr.attribute_id = 183',
        [
            'seller_name' => 'vendor_name_attr.value'
        ]
    );

    // 2. Join for Seller Country (Static Attribute - ID 151)
    // Static attributes main entity table mein hote hain
    $this->getSelect()->joinLeft(
        ['vendor_static' => $this->getTable('ves_vendor_entity')],
        'main_table.vendor_id = vendor_static.entity_id',
        [
            'seller_country' => 'vendor_static.country_id'
        ]
    );

    // 3. Sales Order Join
    $this->getSelect()->joinLeft(
        ['salesorder' => $this->getTable('sales_order')],
        'main_table.order_id = salesorder.entity_id',
        ['salesorder_incid' => 'salesorder.increment_id']
    );

    // Grouping to avoid duplicate rows
    $this->getSelect()->group('main_table.verification_id');
// echo "<pre>";
// print_r($this->getSelect()->__toString()); // SQL Query print karega
// echo "\n\nData Example:\n";
// print_r($this->getConnection()->fetchAll($this->getSelect())); // Actual Data print karega
// die();
    return $this;
}
// Is function ko add karne se data repeat hona band ho jata hai
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->group('main_table.verification_id');
        parent::_renderFiltersBefore();
    }
}