<?php
namespace Business\VisitorcountryReport\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Business\VisitorcountryReport\Model\CountryFactory;

class AddVisitorCountryData implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $countryCollectionFactory;
    protected $visitorCountryFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $countryCollectionFactory,
        CountryFactory $visitorCountryFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->visitorCountryFactory = $visitorCountryFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        /**
         * 1️⃣ Insert countries into custom table (with duplicate check)
         */
        $countries = $this->countryCollectionFactory->create();

        foreach ($countries as $country) {

            
            $exists = $connection->fetchOne(
                "SELECT country_id FROM business_visitorcountry_report_country WHERE country_id = ?",
                [$country->getId()]
            );

            if (!$exists) {
                $this->visitorCountryFactory->create()->setData([
                    'country_id'   => $country->getId(),
                    'country_name' => $country->getName()
                ])->save();
            }
        }

        /**
         * 2️⃣ Insert report event (only if not exists)
         */
        $eventTable = $this->moduleDataSetup->getTable('report_event_types');

        $eventExists = $connection->fetchOne(
            "SELECT event_type_id FROM {$eventTable} WHERE event_name = ?",
            ['visitorcountry_report_visited']
        );

        if (!$eventExists) {
            $connection->insert($eventTable, [
                'event_name'     => 'visitorcountry_report_visited',
                'customer_login' => 0
            ]);
        }

        $this->moduleDataSetup->endSetup();
    }

    public function getAliases()
    {
        return [];
    }
    public static function getDependencies()
{
    return [
        \Business\VisitorcountryReport\Setup\Patch\Schema\CreateVisitorCountryTables::class
    ];
}

}
