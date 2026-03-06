<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Vnecoms\VendorsMessage\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class add customer updated attribute to customer
 */
class InitMessageData implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    private $_customerSetupFactory;

    /**
     * InitMessageData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->_customerSetupFactory->create(
            ['setup' => $this->moduleDataSetup]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'is_block_user',
            [
                'label' => 'Is Block User',
                'type' => 'static',
                'input' => 'text',
                'position' => 145,
                'visible' => false,
                'default' => '',
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'visible_on_front' => false,
            ]
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
