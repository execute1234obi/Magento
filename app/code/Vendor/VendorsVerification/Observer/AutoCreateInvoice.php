<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Backend\App\Area\FrontNameResolver;

class AutoCreateInvoice implements ObserverInterface
{
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;
    protected $resourceConnection;
    protected $logger;
    protected $appState;

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        State $appState
    ) {
        $this->invoiceService      = $invoiceService;
        $this->transaction         = $transaction;
        $this->invoiceSender       = $invoiceSender;
        $this->resourceConnection  = $resourceConnection;
        $this->logger              = $logger;
        $this->appState            = $appState;
    }

    public function execute(Observer $observer)
    {
        try {

            /* 🔒 0. CLI / CRON guard */
            if (php_sapi_name() === 'cli') {
                return;
            }

            /* 🔒 1. Admin area guard */
            try {
                if ($this->appState->getAreaCode() === FrontNameResolver::AREA_CODE) {
                    return;
                }
            } catch (\Exception $e) {
                return;
            }

            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            
            if (!$order || !$order->getId()) {
                return;
            }

            /* 🔒 2. Already invoiced → STOP */
            if ($order->hasInvoices()) {
                return;
            }

            /* 🔒 3. Order state guard */
            if (!in_array($order->getState(), [
                \Magento\Sales\Model\Order::STATE_NEW,
                \Magento\Sales\Model\Order::STATE_PROCESSING
            ])) {
                return;
            }

            /**
             * 🔐 4. Verification-only product check
             */
            $hasVerificationProduct = false;
            $hasOtherProduct        = false;

            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getVendorVerificationId()) {
                    $hasVerificationProduct = true;
                } else {
                    $hasOtherProduct = true;
                }
            }

            // ❌ mixed cart / membership / normal product
            if (!$hasVerificationProduct || $hasOtherProduct) {
                return;
            }

            /* 🔒 5. Payment guard */
            if (!$order->getPayment() || !$order->getPayment()->getMethod()) {
                return;
            }

            /* 🔒 6. Magento invoice condition */
            if (!$order->canInvoice()) {
                return;
            }

            /**
             * 🔥 7. Auto Invoice creation
             */
            $invoice = $this->invoiceService->prepareInvoice($order);

            if (!$invoice || !$invoice->getTotalQty()) {
                return;
            }

            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            // $this->transaction
            //     ->addObject($invoice)
            //     ->addObject($order)
            //     ->save();

            // Sirf invoice ko transaction mein rakhein
$this->transaction->addObject($invoice);

// Order ko save mat karein, bas uski state update karein memory mein
$invoice->getOrder()->setCustomerNoteNotify(true); 

$this->transaction->save();

            // 📧 Send invoice email
            $this->invoiceSender->send($invoice);

            /**
             * 🔄 8. Update verification table
             */
            $this->updateCustomTableStatus($order->getCustomerId());

            // 📝 Order history
           $order->addCommentToStatusHistory(__('Verification fee invoice auto-generated.'));
// Yahan .save() mat lagana. Magento isse khud main transaction ke end mein save kar dega.

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
     * 🔄 Update custom verification table
     */
    private function updateCustomTableStatus($customerId)
    {
        if (!$customerId) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName  = $this->resourceConnection
            ->getTableName('business_vendor_verification');

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
