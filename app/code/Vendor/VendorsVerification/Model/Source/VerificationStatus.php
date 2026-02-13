<?php

namespace Vendor\VendorsVerification\Model\Source;


class VerificationStatus implements \Magento\Framework\Option\ArrayInterface
{
    const VERIFICATION_NOT_VERIFIED    = 0;    
    const VERIFICATION_VERIFIED    = 1;    
    const VERIFICATION_EXPIRED_VERIFIED    = 9;    
    	
	/**
     * @return array
     */
    public function toOptionArray()
    {

        return [            
            ['label' => __('Not Verified'), 'value' => self::VERIFICATION_NOT_VERIFIED],
            ['label' => __('Verified'), 'value' => self::VERIFICATION_VERIFIED],
            ['label' => __('Experied'), 'value' => self::VERIFICATION_EXPIRED_VERIFIED]
            
            
        ];
    }
    
}
