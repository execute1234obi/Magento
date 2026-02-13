<?php

namespace Business\CustomerFieldofinterest\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface {

    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;   


    public function __construct(
      CustomerSetupFactory $customerSetupFactory,
       AttributeSetFactory $attributeSetFactory
     ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        

        $customerSetup->addAttribute(Customer::ENTITY, 'fieldof_interest', [
            'label' => 'Field of Interest',
            'input' => 'select',
            'source' => 'Business\CustomerFieldofinterest\Model\Source\FieldofInterest',
            'required' => true,
            'sort_order' => 999,
            'sort_order' => 999,
            'visible' => true,
            'system' => false,
             'user_defined' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true]
        );

        // add attribute to form
        /** @var  $attribute */
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'fieldof_interest')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            ]);
        $attribute->save();

        $setup->endSetup();
    }

}
