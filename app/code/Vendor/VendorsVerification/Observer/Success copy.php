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
		$this->_logger->info("vefiricationfees_order_success called");
        $order_id = $observer->getEvent()->getData('order_ids');
        $orderid = $order_id[0];
        if(!$orderid) return ;
        $order = $this->orderRepository->get($orderid);
        $orderIncrementId  = $order->getIncrementId();                   
        $orderItems = $order->getAllItems();
        foreach($orderItems as $item){
			$this->_logger->info("vefiricationfees_order_success Item Data=",$item->getData());				
			$verificationId =   $item->getVendorVerificationId();
			if($verificationId){
				$this->messageManager->addSuccess("Your Vendor Verification successfully submit and under process."); 
				$this->_logger->info("vefiricationfees_order_success verificationId=".$verificationId);				
				$vendorVerificationModel = $this->vendorsVerificationFactory->create()->load($verificationId);
				if($vendorVerificationModel->getVerificationId()){
					$vendorVerificationModel->setOrderId($orderid)->save();
				}
				
				 
			}
		}
        
    }
    
                 
        
}
