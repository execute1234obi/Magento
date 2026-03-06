<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMembership\Block\Product\View\Type;

use Vnecoms\VendorsMembership\Model\Source\DurationUnit;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Membership extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils   $arrayUtils
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $arrayUtils, $data);
    }

    /**
     * Get duration label.
     *
     * @param int $duration
     * @param int $unit
     */
    public function getDurationLabel($duration, $unit)
    {
        $label = '';
        switch ($unit) {
            case DurationUnit::DURATION_DAY:
                $label = $duration == 1 ? __('%1 Day', $duration) : __('%1 Days', $duration);
                break;
            case DurationUnit::DURATION_WEEK:
                $label = $duration == 1 ? __('%1 Week', $duration) : __('%1 Weeks', $duration);
                break;
            case DurationUnit::DURATION_MONTH:
                $label = $duration == 1 ? __('%1 Month', $duration) : __('%1 Months', $duration);
                break;
            case DurationUnit::DURATION_YEAR:
                $label = $duration == 1 ? __('%1 Year', $duration) : __('%1 Years', $duration);
                break;
        }

        return $label;
    }

    /**
     * Get option JSON.
     *
     * @return string
     */
    public function getOptionsJSON()
    {
        $options = [];
        $durationOptions = $this->getProduct()->getData('vendor_membership_duration');
        foreach ($durationOptions as $option) {
            $options[] = [
                'label' => $this->getDurationLabel($option['duration'], $option['duration_unit']),
                'value' => $option['duration'].'|'.$option['duration_unit'],
                'price' => $this->convertPrice($option['price']),
            ];
        }

        return json_encode($options);
    }

    /**
     * Has options.
     *
     * @return bool
     */
    public function hasOptions()
    {
        return $this->getProduct()->getTypeInstance()->hasOptions($this->getProduct());
    }

    /**
     * Format price.
     *
     * @param int    $number
     * @param string $includeContainer
     */
    public function formatPrice($number, $includeContainer = false)
    {
        return $this->priceCurrency->format($number, $includeContainer);
    }

    /**
     * Convert the base currency price to current currency.
     *
     * @param float $amount
     *
     * @return float
     */
    public function convertPrice($amount = 0)
    {
        return $this->priceCurrency->convert($amount);
    }

    /**
     * Format price to base currency.
     *
     * @param number $amount
     *
     * @return string
     */
    public function formatBasePrice($amount = 0)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->formatPrecision($amount, 2, [], false);
    }
}
