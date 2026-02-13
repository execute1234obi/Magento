<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart;

class RemoveVerificationFromCart implements ObserverInterface
{
    const VERIFICATION_FEES_PRODUCT_SKU = 'seller_verification_fees';

    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->cart->getQuote();
        $removed = false;

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getSku() === self::VERIFICATION_FEES_PRODUCT_SKU) {
                $quote->removeItem($item->getId());
                $removed = true;
            }
        }

        if ($removed) {
            $quote->collectTotals()->save();
        }
    }
}
