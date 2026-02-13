<?php
namespace Business\VendorVisitorReport\Model\ResourceModel\Profilevisitor\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Psr\Log\LoggerInterface as Logger;

	 
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
	/**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;  
    
    protected $logger;
    
	public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        VendorSession $vendorSession
    ) {
        $mainTable = 'business_vendor_mostview_aggregated';        
        $resourceModel = 'Business\VendorVisitorReport\Model\ResourceModel\Profilevisitor';
        $this->_vendorSession = $vendorSession;
        $this->logger = $logger;
        
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
        $vendorId = $this->_vendorSession->getVendor()->getId();
        /*$this->getSelect()->joinLeft(
            ['visitorcountry_report_country_master'=>$this->getTable('business_visitorcountry_report_country')],
            'visitorcountry_report_country_master.id = main_table.country_id',
            ['country_name'=>'visitorcountry_report_country_master.country_name']
            );*/
        $this->getSelect()->joinLeft(
            ['visitorcountry_report_country_master'=>$this->getTable('business_visitorcountry_report_country')],
            'visitorcountry_report_country_master.id = main_table.mastercountry_id',
            ['country_id'=>'visitorcountry_report_country_master.id']
            );    
        $this->getSelect()->where('main_table.vendor_id = '.$vendorId);        
        $this->getSelect()->group(['period', 'mastercountry_id']);
         //$this->getSelect()->order('views_num','ASC');
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
        $this->logger->info("Reportsql=".$this->getSelect());
       // die($this->getSelect());
        
        return $this;
    }
	     
}
