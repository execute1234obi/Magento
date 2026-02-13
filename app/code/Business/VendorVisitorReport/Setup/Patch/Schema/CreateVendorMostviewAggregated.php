<?php
namespace Business\VendorVisitorReport\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateVendorMostviewAggregated implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
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

        $tableName = $installer->getTable('business_vendor_mostview_aggregated');

        if (!$installer->getConnection()->isTableExists($tableName)) {

            $table = $installer->getConnection()->newTable($tableName)

                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ],
                    'ID'
                )

                ->addColumn(
                    'vendor_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Vendor ID'
                )

                ->addColumn(
                    'vendor_code',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true
                    ],
                    'Vendor Code'
                )

                ->addColumn(
                    'mastercountry_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Country Master ID'
                )

                ->addColumn(
                    'views_num',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default'  => 0
                    ],
                    'Number of Views'
                )

                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Store ID'
                )

                ->addColumn(
                    'period',
                    Table::TYPE_DATE,
                    null,
                    [
                        'nullable' => true
                    ],
                    'Period'
                )

                // INDEXES (as per old DB)
                ->addIndex(
                    $installer->getIdxName($tableName, ['vendor_id']),
                    ['vendor_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['mastercountry_id']),
                    ['mastercountry_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['store_id']),
                    ['store_id']
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['period']),
                    ['period']
                )

                ->setComment('Vendor Profile Visitor Aggregated Report');

            $installer->getConnection()->createTable($table);
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
