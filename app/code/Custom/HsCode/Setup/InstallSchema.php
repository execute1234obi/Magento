<?php
// app/code/Custom/HsCode/Setup/InstallSchema.php
namespace Custom\HsCode\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('custom_hscode')) {
            // Create table for storing HSCode information
            $table = $setup->getConnection()->newTable(
                $setup->getTable('custom_hscode')
            )
                ->addColumn(
                    'hscode_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true, 'unsigned' => true],
                    'HSCode ID'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'HSCode Title'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Country'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' => 'active'],
                    'Status'
                )
                ->addColumn(
                    'pdf',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'PDF File Path'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->setComment('Custom HSCode Table');
            
            // Execute the table creation
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
