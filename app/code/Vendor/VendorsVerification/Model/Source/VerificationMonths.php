<?php

namespace Vendor\VendorsVerification\Model\Source;


class VerificationMonths implements \Magento\Framework\Option\ArrayInterface
{
	
	/**
     * @return array
     */
    public function toOptionArray()
    {

        return [            
            ['value' => 1, 'label' => __('One')],
            ['value' => 2, 'label' => __('Two')],
            ['value' => 3, 'label' => __('Three')],
            ['value' => 4, 'label' => __('Four')],
            ['value' => 5, 'label' => __('Five')],
            ['value' => 6, 'label' => __('Six')],
            ['value' => 7, 'label' => __('Seven')],
            ['value' => 8, 'label' => __('Eight')],
            ['value' => 9, 'label' => __('Nine')],
            ['value' => 10, 'label' => __('Ten')],
            ['value' => 11, 'label' => __('Eleven')],
            ['value' => 12, 'label' => __('Twelve')]
            
        ];
    }
    
}
