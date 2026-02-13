<?php
namespace Vendor\VendorsVerification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;

class Success implements ObserverInterface 
{
	/**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
        protected $vendorsVerificationFactory;
		
		protected $orderRepository;
		
		private $_checkoutSession;
				
		protected $_storeManager;				
        
        protected $messageManager;        
        
        protected $logger;    
		
        public function __construct(        
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
         CheckoutSession $checkoutSession,                        
         VendorVerificationFactory $vendorsVerificationFactory,        
          \Magento\Framework\Message\ManagerInterface $messageManager,
          \Psr\Log\LoggerInterface $logger       
        ){                    
          $this->_storeManager = $storeManager;       
          $this->orderRepository = $orderRepository;            
          $this->_checkoutSession = $checkoutSession;          
          $this->messageManager =  $messageManager;  
          $this->vendorsVerificationFactory = $vendorsVerificationFactory;         
          $this->_logger = $logger;        
         }
         
         
         
public function execute(\Magento\Framework\Event\Observer $observer) {            
    $this->_logger->info("verificationfees_order_success called");
    
    $order_ids = $observer->getEvent()->getData('order_ids');
    if (!$order_ids || !is_array($order_ids)) return;
    
    $orderid = $order_ids[0];
    if(!$orderid) return;

    $order = $this->orderRepository->get($orderid);
    $orderItems = $order->getAllItems();

    foreach($orderItems as $item){
        $verificationId = $item->getVendorVerificationId();
        
        if($verificationId){
            $vendorVerificationModel = $this->vendorsVerificationFactory->create()->load($verificationId);
            
            if($vendorVerificationModel->getVerificationId()){
                // 1. Pehle Order ID save karein
                $vendorVerificationModel->setOrderId($orderid);

                // 2. CHECK: Kya is order ki invoice create ho chuki hai?
                // Magento check karega ki order par koi invoice exist karti hai ya nahi
                if ($order->hasInvoices()) {
                    $this->_logger->info("Invoice already found for Order: " . $orderid);
                    
                    // Agar invoice hai, toh status Paid set karein
                    $vendorVerificationModel->setIsPaid(1);
                    $vendorVerificationModel->setStatus(3); // 3 = Success/Paid
                } else {
                    $this->_logger->info("No invoice found yet for Order: " . $orderid);
                    // Agar invoice abhi nahi bani, toh default status (Pending) rehne dein
                }

                $vendorVerificationModel->save();
                $this->_logger->info("Verification record updated for ID: " . $verificationId);
            }
        }
    }

        
    }
    
                 
        
}
