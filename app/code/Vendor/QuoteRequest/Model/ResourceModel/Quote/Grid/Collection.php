<?php

namespace Vendor\QuoteRequest\Model\ResourceModel\Quote\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable = 'vendor_quote',   // 🔥 YOUR MAIN TABLE
        $resourceModel = \Vendor\QuoteRequest\Model\ResourceModel\Quote::class
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

   protected function _initSelect()
    {
        parent::_initSelect();

        // Customer Name Join
        $this->getSelect()->joinLeft(
            ['ce' => $this->getTable('customer_entity')],
            'main_table.customer_id = ce.entity_id',
            ['customer_name' => new \Zend_Db_Expr("CONCAT(ce.firstname, ' ', ce.lastname)")]
        );

        // Vendor Name Join
        // $this->getSelect()->joinLeft(
        //     ['ve' => $this->getTable('ves_vendor_entity')],
        //     'main_table.vendor_id = ve.entity_id',
        //     ['vendor_name' => 've.company']
        // );
        $this->getSelect()->joinLeft(
    ['cev' => $this->getTable('ves_vendor_entity_varchar')],
    'main_table.vendor_id = cev.entity_id AND cev.attribute_id = 174', // company
    ['vendor_name' => 'cev.value']
);
        // Country Name Join (Custom Table)
        $this->getSelect()->joinLeft(
            ['bc' => $this->getTable('business_visitorcountry_report_country')],
            'main_table.country_id = bc.country_id',
            ['country_name' => 'bc.country_name']
        );

        // Region Name Join
        $this->getSelect()->joinLeft(
            ['r' => $this->getTable('directory_country_region')],
            'main_table.region_id = r.region_id',
            ['region_name' => 'r.default_name']
        );
       //echo $this->getSelect()->__toString();
       //exit;
        return $this;
    }
}