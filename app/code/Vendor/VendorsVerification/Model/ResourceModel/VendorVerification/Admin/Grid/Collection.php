<?php
namespace Vendor\VendorsVerification\Model\ResourceModel\VendorVerification\Admin\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\DB\Select;


class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'business_vendor_verification',
        $resourceModel = 'Vendor\VendorsVerification\Model\ResourceModel\VendorVerification'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_idFieldName = 'verification_id';
    }

 protected function _initSelect()
{
    parent::_initSelect();

    $this->getSelect()->joinLeft(
    ['vendor_name_attr' => $this->getTable('ves_vendor_entity_varchar')],
    'main_table.vendor_id = vendor_name_attr.entity_id
     AND vendor_name_attr.attribute_id = 183',
    ['seller_name' => 'vendor_name_attr.value']
);

    $this->getSelect()->joinLeft(
        ['vendor_static' => $this->getTable('ves_vendor_entity')],
        'main_table.vendor_id = vendor_static.entity_id',
        ['seller_country' => 'vendor_static.country_id']
    );

    $this->getSelect()->joinLeft(
        ['salesorder' => $this->getTable('sales_order')],
        'main_table.order_id = salesorder.entity_id',
        ['salesorder_incid' => 'salesorder.increment_id']
    );
     $this->getSelect()->group('main_table.verification_id');
     // $this->getSelect()->distinct(true);

      //echo $this->getSelect()->__toString();
    //exit;
    return $this;
}
public function getSelectCountSql()
{
    $countSelect = parent::getSelectCountSql();

    $countSelect->reset(Select::GROUP);
    $countSelect->reset(Select::COLUMNS);
    $countSelect->columns('COUNT(DISTINCT main_table.verification_id)');

    return $countSelect;
}


}