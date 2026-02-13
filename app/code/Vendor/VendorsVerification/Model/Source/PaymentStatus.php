<?php

namespace Vendor\VendorsVerification\Model\Source;


class PaymentStatus implements \Magento\Framework\Option\ArrayInterface
{
	
	/**
     * @return array
     */
    public function toOptionArray()
    {

        return [            
            ['value' => 0, 'label' => __('Not Paid')],
            ['value' => 1, 'label' => __('Paid')]
            
            
        ];
    }
    
}
