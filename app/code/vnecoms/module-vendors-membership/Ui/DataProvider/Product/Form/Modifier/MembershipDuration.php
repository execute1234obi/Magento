<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Vnecoms\VendorsMembership\Model\Source\DurationUnit;

/**
 * Data provider for categories field of product page
 */
class MembershipDuration extends AbstractModifier
{


    /**
     * @var DbHelper
     */
    protected $dbHelper;


    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];
    
    /**
     * @var \Vnecoms\Membership\Model\Source\DurationUnit
     */
    protected $_durationUnit;
    
    /**
     * @param LocatorInterface $locator
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param DbHelper $dbHelper
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        DbHelper $dbHelper,
        UrlInterface $urlBuilder,
        DurationUnit $durationUnit,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->dbHelper = $dbHelper;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->_durationUnit = $durationUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        
        $this->customizeMembershipDurationField();

        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Customize tier price field
     *
     * @return $this
     */
    protected function customizeMembershipDurationField()
    {
        $fieldCode = 'vendor_membership_duration';
        $durationPath = $this->arrayManager->findPath(
            $fieldCode,
            $this->meta,
            null,
            'children'
        );
    
        if ($durationPath) {
            $this->meta = $this->arrayManager->merge(
                $durationPath,
                $this->meta,
                $this->getDurationStructure($durationPath)
            );
            
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($durationPath, 0, -3)
                . '/' . $fieldCode,
                $this->meta,
                $this->arrayManager->get($durationPath, $this->meta)
            );
            
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($durationPath, 0, -2),
                $this->meta
            );
        }
    
        return $this;
    }
    
    /**
     * Get Duration Unit.
     * 
     * @return array
     */
    public function getDurationUnits()
    {
        return $this->_durationUnit->getAllOptions();
    }
    
    /**
     * Get duration dynamic rows structure
     *
     * @param string $durationPath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getDurationStructure($durationPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Duration'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                        $this->arrayManager->get($durationPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'duration' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Duration'),
                                        'dataScope' => 'duration',
                                        'validation' => [
                                            'required-entry' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'duration_unit' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'duration_unit',
                                        'label' => __('Duration Unit'),
                                        'options' => $this->getDurationUnits(),
                                    ],
                                ],
                            ],
                        ],
                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'label' => __('Price'),
                                        'enableLabel' => true,
                                        'dataScope' => 'price',
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => false,
                                            'validate-number' => true,
                                        ],
                                        'addbefore' => $this->locator->getStore()
                                        ->getBaseCurrency()
                                        ->getCurrencySymbol(),
                                    ],
                                ],
                            ],
                        ],
                        'sort_order' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Number::NAME,
                                        'label' => __('Sort Order'),
                                        'dataScope' => 'sort_order',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
