<?php
declare(strict_types=1);

namespace Vnecoms\VendorsMembership\Model;

use Magento\Payment\Model\Checks\SpecificationInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Vnecoms\VendorsMembership\Model\Product\Type\Membership;

class MembershipRestriction implements SpecificationInterface
{
    /**
     * @var \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $_helperData;

    /**
     * MembershipRestriction constructor.
     * @param \Vnecoms\VendorsMembership\Helper\Data $helperData
     */
    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $helperData
    ) {
        $this->_helperData = $helperData;
    }
    
    /**
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        $listRestrictPayment = $this->_helperData->getPaymentMethodRetricts();
        if ($listRestrictPayment) {
            $listRestrictPayment = explode("," , $listRestrictPayment);
            if (in_array($paymentMethod->getCode(), $listRestrictPayment) && $this->hasRestrictedItems($quote)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    private function hasRestrictedItems(Quote $quote)
    {
        $hasRestrictedItems = false;
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getTypeId() == Membership::TYPE_CODE) {
                $hasRestrictedItems = true;
                break;
            }
        }
        return $hasRestrictedItems;
    }
}