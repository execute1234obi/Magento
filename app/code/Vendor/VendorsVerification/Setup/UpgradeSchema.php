<?php

namespace Vendor\VendorsVerification\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements  UpgradeSchemaInterface

{
	
	public function upgrade(SchemaSetupInterface $setup,ModuleContextInterface $context)
	{
		$setup->startSetup();
		if (version_compare($context->getVersion(), '1.0.1') < 0) {
			 $setup->getConnection()->addColumn($setup->getTable('quote_item'),
                'vendor_verification_id',                
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Vendor Verification Id',
                ]
            ); 
        }        
        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
			 $setup->getConnection()->addColumn($setup->getTable('sales_order_item'),
                'vendor_verification_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Vendor Verification Id',
                ]
            ); 
        }        
       $setup->endSetup();
      }
}
