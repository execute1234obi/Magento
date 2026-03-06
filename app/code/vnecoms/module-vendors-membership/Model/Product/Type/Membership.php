<?php
/**
 * Credit product type implementation.
 */

namespace Vnecoms\VendorsMembership\Model\Product\Type;

use Vnecoms\VendorsMembership\Model\Source\DurationUnit;

class Membership extends \Magento\Catalog\Model\Product\Type\Virtual
{
    /**
     * Product type code.
     */
    const TYPE_CODE = 'vendor_membership';

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare options belonging to specific product type.
     *
     * @param \Magento\Framework\DataObject  $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string                         $processMode
     *
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $options = $buyRequest->getData('membership');
        $duration = isset($options['duration']) ? $options['duration'] : 0;
        if (!$duration) {
            return __('You need to choose options for your item.')->render();
        }

        list($duration, $durationUnit) = explode('|', $duration);

        $durationOptions = $product->getData('vendor_membership_duration');
        if (!is_array($durationOptions)) {
            $durationOptions = json_decode($durationOptions, true);
        }

        $packagePrice = 0;
        foreach ($durationOptions as $option) {
            if ($duration == $option['duration'] && $durationUnit == $option['duration_unit']) {
                $packagePrice = $option['price'];
            }
        }

        $options['duration'] = $duration;
        $options['duration_unit'] = $durationUnit;
        $options['related_group_id'] = $product->getData('vendor_membership_group_id');
        $options['price'] = $packagePrice;

        $product->addCustomOption('membership', serialize($options));

        return parent::_prepareProduct($buyRequest, $product, $processMode);
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
                $label = $duration == 1 ? __('%1 day', $duration) : __('%1 days', $duration);
                break;
            case DurationUnit::DURATION_WEEK:
                $label = $duration == 1 ? __('%1 week', $duration) : __('%1 weeks', $duration);
                break;
            case DurationUnit::DURATION_MONTH:
                $label = $duration == 1 ? __('%1 month', $duration) : __('%1 months', $duration);
                break;
            case DurationUnit::DURATION_YEAR:
                $label = $duration == 1 ? __('%1 year', $duration) : __('%1 years', $duration);
                break;
        }

        return $label;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);
        if ($attributesOption = $product->getCustomOption('membership')) {
            $data = unserialize($attributesOption->getValue());
            $options['membership'] = $data;
            $options['attributes_info'] = [
                ['label' => __('Duration').'', 'value' => $this->getDurationLabel($data['duration'], $data['duration_unit']).''],
            ];
        }

        return $options;
    }

    /**
     * Return true if product has options.
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function hasOptions($product)
    {
        $duration = $product->getData('vendor_membership_duration');

        return (is_array($duration) && (sizeof($duration) >= 1)) || $product->getHasOptions();
    }
}
