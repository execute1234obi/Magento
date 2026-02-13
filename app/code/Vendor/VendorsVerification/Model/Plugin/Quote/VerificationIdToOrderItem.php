<?php

namespace Vendor\VendorsVerification\Model\Plugin\Quote;

class VerificationIdToOrderItem
{

public function aroundConvert(

        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,

        \Closure $proceed,

        \Magento\Quote\Model\Quote\Item\AbstractItem $item,

        $additional = []

    ) {

        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setVendorVerificationId($item->getVendorVerificationId());
        return $orderItem;

    }

}
