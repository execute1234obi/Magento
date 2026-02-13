<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;

class AutoCreateInvoice implements ObserverInterface
{
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        // HyperPay payment success hone par status 'processing' ho jata hai
        // Hum check karenge ki order processing hai aur pehle se invoiced nahi hai
        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING && $order->canInvoice()) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->register();

                $transactionSave = $this->transaction
                    ->addObject($invoice)
                    ->addObject($order);
                $transactionSave->save();
                

$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
$order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING); // Ya aapka custom success status
$order->save();


                // Email send karein
                $this->invoiceSender->send($invoice);
                
                $order->addStatusHistoryComment(__('HyperPay: Auto-generated Invoice successfully.'))
                      ->setIsCustomerNotified(true)
                      ->save();

            } catch (\Exception $e) {
                // Log error if needed
            }
        }
    }
}