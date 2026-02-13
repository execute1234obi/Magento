<?php
namespace Vendor\CustomConfig\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $entityTypeCode = 'ves_vendor_entity';

        // Add the EAV entity type if it doesn't exist
        $eavSetup->addEntityType(
            $entityTypeCode,
            [
                'entity_model' => 'Vendor\CustomConfig\Model\Vendor',
                'attribute_model' => '',
                'table' => 'ves_vendor_entity',
                'increment_model' => null,
                'entity_type_code' => $entityTypeCode,
            ]
        );

        // Add 'b_name' attribute
        $eavSetup->addAttribute(
            $entityTypeCode,
            'b_name',
            [
                'type' => 'varchar',
                'label' => 'Business Name',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false
            ]
        );

        // Add 'business_descriptions' attribute
        $eavSetup->addAttribute(
            $entityTypeCode,
            'business_descriptions',
            [
                'type' => 'text',
                'label' => 'Business Description',
                'input' => 'textarea',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false
            ]
        );

        // Add 'website' attribute
        $eavSetup->addAttribute(
            $entityTypeCode,
            'website',
            [
                'type' => 'varchar',
                'label' => 'Website',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false
            ]
        );
    }
}