<?php

namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsMembership\Model\Source\DurationUnit;
use Vnecoms\Vendors\Model\Vendor;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class InvoiceSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Vendor\VendorsVerification\Model\VendorVerificationFactory
     */
    protected $vendorsVerificationFactory;

    protected $registry;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;
    
    /**
     * @var \Vnecoms\VendorsMembership\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
     /**
      * @var LoggerInterface
     */
     protected $logger;

      protected $_logger; 

      protected $_checkoutSession;

    public function __construct(
        \Vendor\VendorsVerification\Model\VendorVerificationFactory $vendorsVerificationFactory,        
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\VendorsMembership\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        Registry $registry
    ) {
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
        $this->_transactionFactory = $transactionFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_customerFactory = $customerFactory;
        $this->_date = $date;
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->registry = $registry;
        
    }

    /**     
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_logger->info('=== InvoiceSaveAfterObserver START ===');

// Registry ki jagah Session check karein
if (!$this->_checkoutSession->getIsVendorVerificationFlow()) {
    $this->_logger->info('Not a verification flow, skipping.');
    return;
}

$this->_logger->info('Verification flow detected via Session!');
        /*Return if the invoice is not paid*/
        if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
            return;
        }

        /*Update Vendor Verification */
        $this->processPayVerificationFees($invoice);
        //session variable die
        $this->_checkoutSession->unsIsVendorVerificationFlow();
    }

   /**
     * Process Pay vendor Verification Fees transaction.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    public function processPayVerificationFees(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $customerId = $order->getCustomerId();
        //check for only varification product 
        $hasVerificationItem = false;

        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getVendorVerificationId()) {
                $hasVerificationItem = true;
                break;
            }
        }

        if (!$hasVerificationItem) {
            return;
        }

        if (!$customerId) {
            return;
        }

        $customer = $this->_customerFactory->create();
        $customer->load($customerId);

        if (!$customer->getId()) {
            return;
        }

        $vendor = $this->_vendorFactory->create();
        $vendor->loadByCustomer($customer);
        
        if (!$vendor->getId()) {
            return;
        }
        
        /* Return if the transaction for the invoice already exists. */
        $transCollection = $this->_transactionFactory->create()->getCollection()
            ->addFieldToFilter(
                'additional_info',
                ['like' => 'invoice|'.$invoice->getId().'%']
            );

        if ($transCollection->getSize()) {
            return;
        }

        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            
            // Parent item ko skip karein (Configurable products ke liye)
            if ($orderItem->getParentItemId()) {
                continue;
            }

            // Verification ID check karein jo Order Item mein save hai
            $verificationId = $orderItem->getVendorVerificationId();
            //$this->_logger->info("PRITAM InvoiceSaveAfterObserver checking Verification ID: " . $verificationId);

            if (!$verificationId) {
                continue;
            }
            
            // Verification Model Load karein
            $vendorVerification = $this->vendorsVerificationFactory->create()->load($verificationId);
            
            if ($vendorVerification->getId()) {
                $verificationincId = $vendorVerification->getIncId();
                $months_booked = $vendorVerification->getMonthsBooked();

                // --- Naya Logic Start ---
                // Grid mein dikhane ke liye real Order Number (Increment ID) save karein
                $realOrderNumber = $order->getIncrementId();
                $vendorVerification->setOrderId($realOrderNumber); 
                
                // Status update karein
                $vendorVerification->setIsPaid(1);
                
                // Agar aapko dates abhi set nahi karni to null rakhein
                $vendorVerification->setFromDate(null);
                $vendorVerification->setToDate(null);
                
                // Model save karein
                $vendorVerification->save();
                
              //  $this->_logger->info("PRITAM Success: Order ID " . $realOrderNumber . " saved for Verification " . $verificationincId);
                // --- Naya Logic End ---

                // Vendor Membership Transaction record banayein (Aapka Purana Logic)
                $trans = $this->_transactionFactory->create();
                $trans->setData([
                    'vendor_id'       => $vendor->getId(),
                    'package'         => $item->getName(),
                    'amount'          => $item->getBaseRowTotal(),
                    'verification_id' => $verificationincId,
                    'duration'        => $months_booked,
                    'duration_unit'   => 'Months',
                    'is_other'        => 1,
                    'additional_info' => 'invoice|'.$invoice->getId().'||item|'.$item->getId(),
                    'created_at'      => $this->_date->timestamp(),
                ]);
                $trans->save();
            } else {
                $this->_logger->info("PRITAM Error: Vendor Verification Model not found for ID " . $verificationId);
            }
        }

        // ✅ Final Step: Order ka status update karein taaki wo Admin mein 'Processing' dikhe
        if ($order->getState() !== \Magento\Sales\Model\Order::STATE_PROCESSING) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusHistoryComment(__('HyperPay: Invoice generated and Verification Table updated.'))
                  ->setIsCustomerNotified(true);
            $order->save();
        }
    }
}
