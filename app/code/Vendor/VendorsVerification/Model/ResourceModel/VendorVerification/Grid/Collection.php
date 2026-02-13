<?php
namespace Vendor\VendorsVerification\Model\ResourceModel\VendorVerification\Grid;

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
        
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }


    protected function _construct()
    {
        parent::_construct();
    }
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        /*$this->getSelect()->joinRight(
            ['booking_ad'=>$this->getTable('business_advertisement_booking_ad')],
            'booking_ad.booking_id = main_table.booking_id',
            ['image'=>'ad_image']
        );*/        
        /*$this->getSelect()->join(
            ['booking_space'=>$this->getTable('business_advertisement_space')],
            'booking_space.adspace_id = main_table.adspace_id',
            ['location'=>'name']
        );*/        
        return $this;
    }
	     
}
