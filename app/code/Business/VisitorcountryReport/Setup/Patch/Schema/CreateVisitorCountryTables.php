<?php
namespace Business\VisitorcountryReport\Setup\Patch\Schema;
use Magento\Framework\DB\Adapter\AdapterInterface;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateVisitorCountryTables implements SchemaPatchInterface
{
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $installer = $this->schemaSetup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        /**
         * 1️⃣ Country Master Table
         */
        if (!$connection->isTableExists($installer->getTable('business_visitorcountry_report_country'))) {
            $table = $connection->newTable(
                $installer->getTable('business_visitorcountry_report_country')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                2,
                ['nullable' => false],
                'Country Code'
            )->addColumn(
                'country_name',
                Table::TYPE_TEXT,
                150,
                ['nullable' => true],
                'Country Name'
            )->addForeignKey(
                $installer->getFkName(
                    'business_visitorcountry_report_country',
                    'country_id',
                    'directory_country',
                    'country_id'
                ),
                'country_id',
                $installer->getTable('directory_country'),
                'country_id',
                Table::ACTION_CASCADE
            )->setComment('Business Visitor Country Master');

            $connection->createTable($table);
        }

        /**
         * 2️⃣ Visitor Country Index Table
         */
        if (!$connection->isTableExists($installer->getTable('business_report_visitor_country_index'))) {
            $table = $connection->newTable(
                $installer->getTable('business_report_visitor_country_index')
            )->addColumn(
                'index_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Index ID'
            )->addColumn(
                'visitor_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Visitor ID'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Customer ID'
            )->addColumn(
                'visitor_ip',
                Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'Visitor IP'
            )->addColumn(
                'visitor_country',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Visitor Country'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Store ID'
            )->addColumn(
                'added_at',
                Table::TYPE_DATE,
                null,
                ['nullable' => true, 'default' => null],
                'Added At'
            )->addForeignKey(
                $installer->getFkName(
                    'business_report_visitor_country_index',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'business_report_visitor_country_index',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addIndex(
            $installer->getIdxName(
            $installer->getTable('business_report_visitor_country_index'),
            ['store_id'],
            AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['store_id']
            
            )->addIndex(
            $installer->getIdxName(
            $installer->getTable('business_report_visitor_country_index'),
            ['added_at'],
            AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['added_at']
            )->addIndex(
                $installer->getIdxName(
                    'BUSINESS_REPORT_VISITOR_COUNTRY_UNIQUE',
                    ['visitor_id', 'visitor_ip', 'visitor_country', 'added_at'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['visitor_id', 'visitor_ip', 'visitor_country', 'added_at'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Business Visitor Country Index');

            $connection->createTable($table);
        }

        /**
         * 3️⃣ Aggregated Table
         */
        if (!$connection->isTableExists($installer->getTable('business_visitor_country_aggregated'))) {
            $table = $connection->newTable(
                $installer->getTable('business_visitor_country_aggregated')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'country_code',
                Table::TYPE_TEXT,
                10,
                ['nullable' => true],
                'Country Code'
            )->addColumn(
                'country_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Country Master ID'
            )->addColumn(
                'visitors_num',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Visitors Count'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Store ID'
            )->addColumn(
                'period',
                Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Period'
            )->addForeignKey(
                $installer->getFkName(
                    'business_visitor_country_aggregated',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addIndex(
                $installer->getIdxName(
                    'BUSINESS_VISITOR_COUNTRY_AGG_UNIQUE',
                    ['period', 'store_id', 'country_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['period', 'store_id', 'country_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Business Visitor Country Aggregated');

            $connection->createTable($table);
        }

        $installer->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
