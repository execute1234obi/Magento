<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\ProductHasprice\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form;

class HasPrice extends AbstractModifier
{
    /**
     * @var   LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var   ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param LocatorInterface                  $locator
     * @param ArrayManager                      $arrayManager
     * @param AttributeRepositoryInterface|null $attributeRepository
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        AttributeRepositoryInterface $attributeRepository = null
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->attributeRepository = $attributeRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
    }
    
    /**
    * @param array $meta
    *
    * @return array
    */
    public function modifyMeta(array $meta): array
    {
		$meta = $this->customizePriceField($meta);
		return $meta;
        /*$meta['general'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Label For Fieldset'),
                        'sortOrder' => 50,
                        'collapsible' => true,
                        'componentType' => Fieldset::NAME
                    ]
                ]
            ],
            'children' => [
                'test_field_name' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'select',
                                'componentType' => Field::NAME,
                                'options' => [
                                    ['value' => 'test_value_1', 'label' => 'Test Value 1'],
                                    ['value' => 'test_value_2', 'label' => 'Test Value 2'],
                                    ['value' => 'test_value_3', 'label' => 'Test Value 3'],
                                ],
                                'visible' => 1,
                                'required' => 1,
                                'label' => __('Label For Element'),
                                'onChange'  => 'alert("got it")',
                            ]
                        ]
                    ]
                ]
            ]
        ];*/

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
		
        return $data;
    }
    
    protected function customizePriceField(array $meta)
    {
		//echo json_encode($meta);
	    //echo "<pre>".print_r($meta, 1)."</pre>";
		//die;
    /*$meta['product-details']['children']['container_price']['children']['price']['arguments']['data']['config']['disabled'] = true;    
    $meta['advanced-pricing']['arguments']['data']['config']['label'] = 'PRITAM';    
    return $meta;*/
        $weightPath = $this->arrayManager->findPath(ProductAttributeInterface::CODE_PRICE, $meta, null, 'children');    
        $disabled = $this->arrayManager->get($weightPath . '/arguments/data/config/disabled', $meta);
        if ($weightPath) {
			
            $meta = $this->arrayManager->merge(
                $weightPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataScope' => ProductAttributeInterface::CODE_PRICE,
                    'validation' => [
                        'validate-zero-or-greater' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'sortOrder' => 0,                    
                    //'addafter' => $this->locator->getStore()->getConfig('general/locale/weight_unit'),
                    'imports' => $disabled ? [] : [
                        'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT. '.product_has_price:value',
                        '__disableTmpl' => ['disabled' => false],
                    ]
                ]
            );

            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_PRICE,
                $meta,
                null,
                'children'
            );
            $meta = $this->arrayManager->merge(
                $containerPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'label' => false,
                    'required' => false,
                    'component' => 'Magento_Ui/js/form/components/group',                    
                ]
            );

            $hasPricePath = $this->arrayManager->slicePath($weightPath, 0, -1) . '/'
                . 'producthasprice';
            $productPrice =  $this->locator->getProduct()->getPrice();                
            $showProdctPrice = (isset($productPrice) && $productPrice  > 0 ) ? 1:0;                
            $meta = $this->arrayManager->set(
                $hasPricePath . static::META_CONFIG_PATH,
                $meta,
                [

                    'dataType' => 'boolean',
                    'formElement' => Form\Element\Select::NAME,
                    'componentType' => Form\Field::NAME,                 
                    'component' => 'Business_ProductHasprice/js/components/price-select',   
                    'dataScope' => 'product_has_price',
                    //'elementTmpl' => 'ui/grid/filters/elements/ui-select',                    
                    'elementTmpl' => 'ui/form/element/select',
                    'label' => '',
                    'caption'=> '',
                    'options' => [
                        [
                            'label' => __('This item has Price'),
                            'value' => 1
                        ],
                        [
                            'label' => __('This item has no Price'),
                            'value' => 0
                        ],
                    ],
                    'value' => $showProdctPrice,                    
                    'sortOrder' => 10,
                    'disabled' => $disabled
                    
                ]
            );
        }
        
        //echo "<pre>".print_r($meta, 1)."</pre>";
        //echo json_encode($meta);
		//die;

        return $meta;
    }

}
