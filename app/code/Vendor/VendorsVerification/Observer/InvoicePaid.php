<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;

class InvoicePaid implements ObserverInterface
{
    protected $vendorsVerificationFactory;

    public function __construct(
        VendorVerificationFactory $vendorsVerificationFactory
    ) {
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();

    if (!$invoice || !$invoice->getId()) {
        return;
    }

    if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
        return;
    }

    $order = $invoice->getOrder();
        foreach ($order->getAllItems() as $item) {

            $verificationId = $item->getVendorVerificationId();
            if (!$verificationId) {
                continue;
            }

            $verification = $this->vendorsVerificationFactory
                ->create()
                ->load($verificationId);

            if (!$verification->getId()) {
                continue;
            }

            // ✅ THIS WAS MISSING
            $verification->setIsPaid(1);
            $verification->setOrderId($order->getId());
            $verification->setStatus(2); // Paid / In Progress
            $verification->save();
        }
    }
}
