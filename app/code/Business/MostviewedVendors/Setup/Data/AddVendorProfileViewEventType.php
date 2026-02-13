<?php

namespace Business\MostviewedVendors\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddVendorProfileViewEventType implements DataPatchInterface
{
    const EVENT_NAME = 'vendorspage_controller_router_match_vendor';

    private $setup;

    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply()
    {
        $this->setup->startSetup();

        $table = $this->setup->getTable('report_event_types');
        $connection = $this->setup->getConnection();

        // 🔒 Check if already exists
        $select = $connection->select()
            ->from($table, 'event_type_id')
            ->where('event_name = ?', self::EVENT_NAME);

        if (!$connection->fetchOne($select)) {
            $connection->insert(
                $table,
                [
                    'event_name' => self::EVENT_NAME,
                    'customer_login' => 0
                ]
            );
        }

        $this->setup->endSetup();
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
