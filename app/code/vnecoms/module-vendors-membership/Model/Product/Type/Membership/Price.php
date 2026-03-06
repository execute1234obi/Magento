<?php
/**
 * Credit product type implementation.
 */

namespace Vnecoms\VendorsMembership\Model\Product\Type\Membership;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * {@inheritdoc}
     */
    public function getPrice($product)
    {
        $price = 0;

        $priceOptions = $product->getData('vendor_membership_duration');
        if (!$priceOptions) {
            $priceOptions = $product->load($product->getId())
                            ->getData('vendor_membership_duration');
        }

        if (!is_array($priceOptions)) {
            $priceOptions = json_decode($priceOptions, true);
        }

        $firstOption = current($priceOptions);
        $price = $firstOption['price'];

        /*Get lowest Price*/
        foreach ($priceOptions as $option) {
            if ($option['price'] < $price) {
                $price = $option['price'];
            }
        }

        $product->setData('price', $price);

        return parent::getPrice($product);
    }

    /**
     * Get base price with apply Group, Tier, Special prises.
     *
     * @param Product    $product
     * @param float|null $qty
     *
     * @return float
     */
    public function getBasePrice($product, $qty = null)
    {
        $membership = $product->getCustomOption('membership');
        if (!$membership) {
            return parent::getBasePrice($product, $qty);
        }

        $membership = unserialize($membership->getValue());

        $price = isset($membership['price']) ? $membership['price'] : 0;

        return $price;
    }
}
