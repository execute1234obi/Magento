<?php
namespace Vendor\VendorsVerification\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
//use Magento\Directory\Model\Config\Source\Country;
use Vendor\VendorsVerification\Model\Source\Countries;


class UpdateVendorCountrySource implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $eavSetup = $this->eavSetupFactory->create([
            'setup' => $this->moduleDataSetup
        ]);

        // 🔥 IMPORTANT PART
        $eavSetup->updateAttribute(
            'vendor',                 // VNECOM vendor entity
            'country_id',              // attribute code
            'source_model',
            //Countries::class             // Magento full country list
             \Vendor\VendorsVerification\Model\Source\Countries::class
        );

        $this->moduleDataSetup->endSetup();
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
