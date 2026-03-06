<?php

namespace Vnecoms\VendorsMembership\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Vnecoms\Vendors\Model\Vendor;
use Vnecoms\VendorsMembership\Model\Product\Type\Membership;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class InitializeVendorsMembershipAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Customer setup factory.
     *
     * @var \Vnecoms\Vendors\Setup\VendorSetupFactory
     */
    private $_vendorSetupFactory;

    /**
     * InitializeVendorsMembershipAttributes constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Vnecoms\Vendors\Setup\VendorSetupFactory $vendorSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        \Vnecoms\Vendors\Setup\VendorSetupFactory $vendorSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->_vendorSetupFactory = $vendorSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $categorySetup->addAttribute(
            Product::ENTITY,
            'vendor_membership_group_id',
            [
                'group' => 'Product Details',
                'label' => 'Related Vendor Group',
                'type' => 'int',
                'input' => 'select',
                'position' => 100,
                'visible' => true,
                'default' => '',
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'source' => 'Vnecoms\VendorsMembership\Model\Source\Group',
                'default' => '',
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                'used_for_promo_rules' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'apply_to' => Membership::TYPE_CODE,
            ]
        );
        $categorySetup->addAttribute(
            Product::ENTITY,
            'vendor_membership_duration',
            [
                'group' => 'Product Details',
                'label' => 'Duration',
                'type' => 'text',
                'input' => 'text',
                'position' => 101,
                'visible' => true,
                'default' => '',
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'backend' => 'Vnecoms\VendorsMembership\Model\Product\Attribute\Backend\Duration',
                'default' => '',
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                'used_for_promo_rules' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'apply_to' => Membership::TYPE_CODE,
            ]
        );

        $categorySetup->addAttribute(
            Product::ENTITY,
            'vendor_membership_feature',
            [
                'group' => 'Product Details',
                'label' => 'Is Featured Package',
                'type' => 'int',
                'input' => 'boolean',
                'position' => 102,
                'visible' => true,
                'default' => '0',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'default' => '',
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                'used_for_promo_rules' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'apply_to' => Membership::TYPE_CODE,
            ]
        );

        $categorySetup->addAttribute(
            Product::ENTITY,
            'vendor_membership_sort_order',
            [
                'group' => 'Product Details',
                'label' => 'Package Sort Order',
                'type' => 'int',
                'input' => 'text',
                'position' => 103,
                'visible' => true,
                'default' => '',
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false,
                'used_for_promo_rules' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'frontend_class' => 'validate-digits',
                'apply_to' => Membership::TYPE_CODE,
            ]
        );

        /*make sure these attributes are applied for membership product type only*/
        $attributes = [
            'vendor_membership_group_id',
            'vendor_membership_duration',
            'vendor_membership_feature',
            'vendor_membership_sort_order',
        ];
        foreach ($attributes as $attributeCode) {
            $attribute = $categorySetup->getAttribute(Product::ENTITY, $attributeCode);
            $categorySetup->updateAttribute(Product::ENTITY, $attributeCode, 'apply_to', Membership::TYPE_CODE);
        }

        $fieldList = [
            'tax_class_id',
            'quantity_and_stock_status',
        ];

        // make these attributes applicable to vendor membership products
        foreach ($fieldList as $field) {
            $applyTo = $categorySetup->getAttribute(Product::ENTITY, $field, 'apply_to');
            if(!$applyTo) continue;

            $applyTo = explode(
                ',',
                $applyTo
            );
            if (!in_array(Membership::TYPE_CODE, $applyTo)) {
                $applyTo[] = Membership::TYPE_CODE;
                $categorySetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $vendorSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        $vendorSetup->addAttribute(
            Vendor::ENTITY,
            'expiry_date',
            [
                'type' => 'static',
                'label' => 'Expiry Date',
                'input' => 'date',
                'position' => 145,
                'visible' => true,
                'required' => false,
                'default' => '',
                'user_defined'=>0,
                'system' => 0,
                'used_in_profile_form' => 1,
                'used_in_registration_form' => 0,
                'visible_in_customer_form' => 0,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
