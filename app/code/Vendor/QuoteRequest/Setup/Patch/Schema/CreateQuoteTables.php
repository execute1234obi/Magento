<?php

namespace Vendor\QuoteRequest\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateQuoteTables implements SchemaPatchInterface
{
    private $setup;

    public function __construct(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    public function apply()
    {
        $setup = $this->setup;
        $setup->startSetup();

        $connection = $setup->getConnection();

        /*
        vendor_quote table
        */

        if (!$connection->isTableExists($setup->getTable('vendor_quote'))) {

            $table = $connection->newTable(
                $setup->getTable('vendor_quote')
            )->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true]
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true]
            )->addColumn(
                'vendor_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true]
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => 'pending']
            )->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                10,
                ['nullable' => true]
            )->addColumn(
                'region_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )->addColumn(
                'customer_note',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
            )->addIndex(
                $setup->getIdxName('vendor_quote', ['customer_id']),
                ['customer_id']
            )->addIndex(
                $setup->getIdxName('vendor_quote', ['vendor_id']),
                ['vendor_id']
            );

            $connection->createTable($table);
        }


        /*
        vendor_quote_item table
        */

        if (!$connection->isTableExists($setup->getTable('vendor_quote_item'))) {

            $table = $connection->newTable(
                $setup->getTable('vendor_quote_item')
            )->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true]
            )->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )->addColumn(
                'proposed_price',
                Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => true]
            )->addIndex(
                $setup->getIdxName('vendor_quote_item', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $setup->getFkName(
                    'vendor_quote_item',
                    'quote_id',
                    'vendor_quote',
                    'quote_id'
                ),
                'quote_id',
                $setup->getTable('vendor_quote'),
                'quote_id',
                Table::ACTION_CASCADE
            );

            $connection->createTable($table);
        }

        $setup->endSetup();
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