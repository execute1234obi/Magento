<?php

namespace Vendor\VendorsVerification\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class VendorTypes implements OptionSourceInterface
{
    const BUSINESS_TYPE_1 = 1;
    const BUSINESS_TYPE_2 = 2;
    const BUSINESS_TYPE_3 = 3;
    const BUSINESS_TYPE_4 = 4;
    const BUSINESS_TYPE_5 = 5;
    const BUSINESS_TYPE_6 = 6;
    const BUSINESS_TYPE_7 = 7;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Wholesale'),     'value' => self::BUSINESS_TYPE_1],
            ['label' => __('Retail'),        'value' => self::BUSINESS_TYPE_2],
            ['label' => __('Manufacturing'), 'value' => self::BUSINESS_TYPE_3],
            ['label' => __('Food'),          'value' => self::BUSINESS_TYPE_4],
            ['label' => __('Electronic'),    'value' => self::BUSINESS_TYPE_5],
            ['label' => __('Decor'),         'value' => self::BUSINESS_TYPE_6],
            ['label' => __('Service'),       'value' => self::BUSINESS_TYPE_7],
        ];
    }
}
