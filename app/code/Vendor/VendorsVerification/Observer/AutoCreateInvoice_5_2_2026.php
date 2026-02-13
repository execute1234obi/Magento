<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface;

class AutoCreateInvoice implements ObserverInterface
{
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->invoiceService      = $invoiceService;
        $this->transaction         = $transaction;
        $this->invoiceSender       = $invoiceSender;
        $this->resourceConnection  = $resourceConnection;
        $this->logger              = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();

            if (!$order || !$order->getId()) {
                return;
            }

            /**
             * 🔐 STEP 1:
             * Sirf verification-only product ke liye allow
             */
            $hasVerificationProduct = false;
            $hasOtherProduct        = false;

            foreach ($order->getAllVisibleItems() as $item) {

                // 🔑 Verification product condition
                // (attribute / custom column jo tum use kar rahe ho)
                if ($item->getVendorVerificationId()) {
                    $hasVerificationProduct = true;
                } else {
                    $hasOtherProduct = true;
                }
            }

            // ❌ Mixed OR normal order → STOP auto invoice
            if (!$hasVerificationProduct || $hasOtherProduct) {
                $this->logger->info(
                    'AUTO-INVOICE SKIPPED | Order #' . $order->getIncrementId()
                );
                return;
            }

            /**
             * 🔐 STEP 2:
             * Magento invoice condition
             */
            if (!$order->canInvoice()) {
                return;
            }

            /**
             * 🔥 STEP 3:
             * Auto Invoice create
             */
            $invoice = $this->invoiceService->prepareInvoice($order);

            if (!$invoice || !$invoice->getTotalQty()) {
                return;
            }

            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            $this->transaction
                ->addObject($invoice)
                ->addObject($order)
                ->save();

            // 📧 Invoice email
            $this->invoiceSender->send($invoice);

            /**
             * 🔄 STEP 4:
             * Custom verification table update
             */
            $this->updateCustomTableStatus($order->getCustomerId());

            // 📝 Order history note
            $order->addStatusHistoryComment(
                __('Verification product: Invoice auto-generated successfully.')
            )->setIsCustomerNotified(true)->save();

            $this->logger->info(
                'AUTO-INVOICE SUCCESS | Order #' . $order->getIncrementId()
            );

        } catch (\Exception $e) {
            $this->logger->critical(
                'AUTO-INVOICE ERROR | ' . $e->getMessage()
            );
        }
    }

    /**
     * 🔄 Custom table update after successful invoice
     */
    private function updateCustomTableStatus($customerId)
    {
        if (!$customerId) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName  = $this->resourceConnection->getTableName(
            'business_vendor_verification'
        );

        $connection->update(
            $tableName,
            [
                'is_paid' => 1,
                'status'  => 3
            ],
            ['customer_id = ?' => (int)$customerId]
        );
    }
}
