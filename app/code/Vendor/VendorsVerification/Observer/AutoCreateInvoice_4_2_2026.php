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
    protected $resourceConnection; // Custom table update ke liye

    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
     $orderId = $order->getId(); // Database ki Primary ID (e.g., 69)
    $incrementId = $order->getIncrementId(); // Magento ka Display ID (e.g., 000000074)

//     echo "<pre>";
//     echo "--- ORDER DATA DEBUG --- \n";
//     echo "Order Entity ID (matches your DB order_id): " . $orderId . "\n";
//     echo "Order customer ID: " . $order->getCustomerId() . "\n";
//     echo "Current Order Status: " . $order->getStatus() . "\n";
//     echo "Current Order State: " . $order->getState() . "\n";
    
//     // Agar aapko order ke andar ke sabhi 'Data' fields dekhne hain (bina recursion ke)
//   echo "<pre>";
// // Sirf keys print karein (names of columns)
// print_r(array_keys($order->getData())); 
// echo "</pre>";
//     echo "</pre>";
//     die("Stopping execution to see output of order.");
        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING && $order->canInvoice()) {
            try {
                // 1. Invoice Taiyar karein
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->register();

                // 2. Order aur Invoice save karein
                $transactionSave = $this->transaction
                    ->addObject($invoice)
                    ->addObject($order);
                $transactionSave->save();

                // 3. Email send karein
                $this->invoiceSender->send($invoice);
                // --- IS PAID STATUS CHANGE LOGIC ---
               // $this->updateCustomTableStatus($order->getCustomerId());

                $order->addStatusHistoryComment(__('HyperPay: Invoice created and Payment marked as Paid in Verification table.'))
                      ->setIsCustomerNotified(true)
                      ->save();

            } catch (\Exception $e) {
                // Agar error aaye toh aap chahein toh status update kar sakte hain
                $order->addStatusHistoryComment(__('Error updating Payment Status: ' . $e->getMessage()))
                      ->save();
            }
        }
    }
    private function updateCustomTableStatus($CustomerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('business_vendor_verification');

        // is_paid = 1 (Paid), status = 3 (Success/Verified)
        // Aapke DB screenshot ke mutabiq inc_id column use kiya gaya hai
        $data = [
            'is_paid' => 1,
            'status'  => 3 
        ];
        
       $where = ['customer_id = ?' => $CustomerId];
             // Ye sirf testing ke liye hai
// print_r($data);
// print_r($where);
// echo "Updating Table: " . $tableName;
// die("Stopping execution to see output.");
        $connection->update($tableName, $data, $where);
   
    }

}