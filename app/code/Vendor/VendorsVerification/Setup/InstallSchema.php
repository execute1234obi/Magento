<?php

namespace Vendor\VendorsVerification\Setup;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Vendor\BookAdvertisement\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;
    

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * InstallSchema constructor.
     *     
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(        
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;        
        $this->fileSystem = $filesystem;
    }


    /**
     * install tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('business_vendor_verification')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('business_vendor_verification'))
                ->addColumn(
                    'verification_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ],
                    'Vendor Verification  ID'
                )
                ->addColumn('vendor_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Vendor ID')
                ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Customer ID')
                ->addColumn('inc_id',  Table::TYPE_TEXT, 255, ['nullable' => true], 'Incremental ID')                
                ->addColumn('website_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Vendor Website ID')
                ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Vendor Website ID')
                ->addColumn('months_booked', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'AD Days Booked')
                ->addColumn('country', Table::TYPE_TEXT, 50, ['nullable' => false,'default' => ''], 'country code')
                ->addColumn('order_id', Table::TYPE_INTEGER, null, ['nullable' => null, 'default' => '0'], 'Order ID')
                ->addColumn('amount', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '0'], 'Amout')
                ->addColumn('is_paid', Table::TYPE_BOOLEAN, null, [ 'identity' => false, 'default' => false,'nullable' => false ], 'Is Verification Fee paid')                
                ->addColumn('is_active', Table::TYPE_BOOLEAN, null, [ 'identity' => false, 'default' => false,'nullable' => false ], 'Is Active')                
                ->addColumn('from_date', Table::TYPE_DATE, null, ['nullable' => true], 'From')
                ->addColumn('to_date', Table::TYPE_DATE, null, ['nullable' => true], 'To')                
                ->addColumn('is_verified', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Is Verified')                      
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Status')                
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Review Created At')                                
                ->setComment('Vendor  Verification Table');
                $installer->getConnection()->createTable($table);
        }   
        
        
        if (!$installer->tableExists('business_vendor_verification_data')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('business_vendor_verification_data'))
                ->addColumn(
                    'detail_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ],
                    'Id'
                )                
                ->addColumn('verification_id', Table::TYPE_INTEGER, null, ['unsigned'=> true,'nullable' => false], 'Verification Id')
                ->addColumn('datagroup_id', Table::TYPE_INTEGER, null, ['unsigned'=> true,'nullable' => false], 'Data Grop Id')
                ->addColumn('vendor_data', Table::TYPE_TEXT, '64k', [], 'Vendor Data')                
                ->addColumn('approval', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Approval')
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')                
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addForeignKey(
                    $installer->getFkName(
                        'business_vendor_verification_data',
                        'verification_id',
                        'business_vendor_verification',
                        'verification_id'
                    ),
                    'verification_id',
                    $installer->getTable('business_vendor_verification'),
                    'verification_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Vendor Verification Data  Table');
                $installer->getConnection()->createTable($table);
        }   
        
        
        if (!$installer->tableExists('business_vendor_verification_comments')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('business_vendor_verification_comments'))
                ->addColumn(
                    'comment_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ],
                    'Id'
                )                                
                ->addColumn('verification_id', Table::TYPE_INTEGER, null, ['unsigned'=> true,'nullable' => false], 'Verification Id')
                ->addColumn('detail_id', Table::TYPE_INTEGER, null, ['unsigned'=> true,'nullable' => false], 'Details data Id')
                ->addColumn('datagroup_id', Table::TYPE_INTEGER, null, ['unsigned'=> true,'nullable' => false], 'Data Grop Id')
                ->addColumn('comment', Table::TYPE_TEXT, '64k', [], 'Vendor Data')                
                ->addColumn('vendor_dataupdate', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Vendor Data History')                
                ->addColumn('admin_userid', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Admin User ID')
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')                
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')                
                ->addForeignKey(
                    $installer->getFkName(
                        'business_vendor_verification_comments',
                        'detail_id',
                        'business_vendor_verification_data',
                        'detail_id'
                    ),
                    'detail_id',
                    $installer->getTable('business_vendor_verification_data'),
                    'detail_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Vendor Verification Comments  Table');
                $installer->getConnection()->createTable($table);
        }   

        $installer->endSetup();
    }
    
    
    
}
