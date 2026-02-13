<?php
namespace Custom\CustomerBusinessCategory\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;

class AddCustomerBusinessCategory implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

   // In file: app/code/Custom/CustomerBusinessCategory/Setup/Patch/Data/AddCustomerBusinessCategory.php

public function apply()
{
    $this->moduleDataSetup->getConnection()->startSetup();
    $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

    $customerSetup->addAttribute(
        \Magento\Customer\Model\Customer::ENTITY,
        'field_of_interest', // Corrected name for the customer attribute
        [
            'type' => 'varchar',
            'label' => 'Field of Interest',
            'input' => 'select',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'position' => 210,
            'system' => 0,
            'source' => \Vnecoms\Vendors\Model\Attribute\Source\BusinessCategory::class, // Vnecoms source model
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
        ]
    );

    $attribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'field_of_interest');
    $attribute->addData([
        'used_in_forms' => [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]
    ]);
    $attribute->save();

    $this->moduleDataSetup->getConnection()->endSetup();
}


    public static function getDependencies() {
        return [];
    }

    public function getAliases() {
        return [];
    }
}
