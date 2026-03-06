<?php

namespace Vnecoms\VendorsMembership\Model\Product\TypeTransitionManager\Plugin;

use Closure;
use Magento\Framework\App\RequestInterface;

class Membership
{
    /**
     * Change product type to configurable if needed
     *
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $subject
     * @param Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcessProduct(
        \Magento\Catalog\Model\Product\TypeTransitionManager $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getTypeId() == \Vnecoms\VendorsMembership\Model\Product\Type\Membership::TYPE_CODE) {
            return;
        }
        $proceed($product);
    }
}