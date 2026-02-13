<?php
namespace Vendor\VendorsVerification\Controller\Vendors\Quotes;


use Vnecoms\Vendors\Controller\Vendors\Action;
//use Vnecoms\Vendors\App\Action\Context; // Context change kiya
//use Magento\Framework\App\Action\Context;
use Vnecoms\Vendors\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductFactory;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\Registry;


class Addtocart extends Action
{
    const VERIFICATION_FEES_PRODUCT_SKU = 'seller_verification_fees';

    protected $cart;
    protected $productFactory;
    protected $productRepository;
    protected $vendorsVerificationFactory;
    protected $storeManager;
    protected $serializer;
    protected $helper;
    protected $_vendorSession;
    protected $registry;
    protected $_checkoutSession;
   public function __construct(
    Context $context,
    Cart $cart,
    VendorSession $vendorSession,
    ProductFactory $productFactory,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    VendorVerificationFactory $vendorsVerificationFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Serialize\SerializerInterface $serializer,
    \Vendor\VendorsVerification\Helper\Data $helper,
    \Magento\Checkout\Model\Session $checkoutSession,
    Registry $registry
) {
    parent::__construct($context);
        $this->cart = $cart;
        $this->_vendorSession = $vendorSession;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->vendorsVerificationFactory = $vendorsVerificationFactory;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->helper = $helper;
         $this->registry = $registry;
         $this->_checkoutSession = $checkoutSession;
    }

    public function execute() {
       		try {
            
          // Registry wali line ko hata kar ye likhein:
            $this->_checkoutSession->setIsVendorVerificationFlow(true);
			$storeId = $this->getRequest()->getParam('store', 0);
			$verificationId = $this->getRequest()->getParam('id');		
			if (!$this->_vendorSession->isLoggedIn()) {				
				return $this->_redirect('customer/account/login');                
			}
             
			$vendor =  $this->_vendorSession->getVendor();
			$customer = $this->_vendorSession->getCustomer();
            $customer_id = $customer->getId();
            $vendor_id = $this->_vendorSession->getVendor()->getId();
            
            $verifiactionFess_config =  $this->helper->getConfig('Vendor_vendorverification/config/fees',$this->storeManager->getStore()->getCode());
            $verifiactionMonths = (int) $this->helper->getConfig('Vendor_vendorverification/config/months_duration',$this->storeManager->getStore()->getCode());
            $vendorCountryCode =  $vendor->getData('country_id');
            if($vendorCountryCode == ''){
				$this->_messageManager->addError(__("Vendor Country is not set.Unable to process Seller Verification."));
                return $this->_redirect('vendors/vendorverification/verification/index/');
			}else if($verifiactionMonths <= 0){
				$this->_messageManager->addError(__("Seller Verification Months is not set.Unable to process Seller Verification."));
                return $this->_redirect('vendors/vendorverification/verification/index/');
			}
            $verificationFees = 0;
            
            if($verifiactionFess_config != '' && $vendorCountryCode != ''){
                $verifiactionFess_configData = $this->helper->jsonToArray($verifiactionFess_config);
                foreach($verifiactionFess_configData as $key=>$value):
                if( strtoupper($value['countrycode']) == strtoupper($vendorCountryCode) ){
					$verificationFees = (float) $value['fees'];
				}                   
                endforeach;   
		     }            
		     
            if($verificationFees <= 0){
				$this->_messageManager->addError(__("Verification Fees not exist. Unable to process Seller Verification."));
                return $this->_redirect('vendors/vendorverification/verification/index/');
			}
			
            $vendorVerification = $this->vendorsVerificationFactory->create()->load($verificationId);
            $vendorsVerificationIncId = $vendorVerification->getIncId();
            $vendorCountry = $vendorVerification->getCountry();
            /*echo "<pre>".print_r($vendorVerification->getData(), 1)."</pre>";
            echo "vendor Country=".$vendorCountry;     
            echo "Vendor Coyntry=".$vendorCountryCode."<br />";
		    echo "verificationFees = ".$verificationFees."<br />";
		    echo "verificationMohts = ".$verifiactionMonths."<br />";*/
            
            
            if($vendor_id != $vendorVerification->getData('vendor_id')){//Vendor Own the Verification
				 $this->messageManager->addErrorMessage(__('Invalid action.'));
			     return $this->_redirect ('vendors/vendorverification/verification/' );
			}
			if ($vendorVerification->getData('is_paid') != 0) {
				 $this->messageManager->addErrorMessage(__('Invalid action.Unable to process payment.'));
			     return $this->_redirect ('vendors/vendorverification/verification/' );
				}			
		    
// 			if($this->isQuoteItemExist($verificationId)){ 
//             $this->messageManager->addErrorMessage(__("Verification Fees already exist in cart."));
//     //return $this->resultRedirectFactory->create()->setPath('checkout/cart/index');
//             $cartUrl = $this->storeManager
//     ->getStore()
//     ->getUrl('checkout/cart', ['_scope_to_url' => true]);

// return $this->resultRedirectFactory
//     ->create()
//     ->setUrl($cartUrl);
// }

	if($this->addVerificationFeestoQuote($verificationId)){
    // 'setPath' ke bajaye 'setUrl' use karein taaki full frontend URL bane
    $baseUrl = $this->storeManager->getStore()->getBaseUrl();
    //$checkoutUrl = $baseUrl . 'checkout/index/index';
    $checkoutUrl = $baseUrl . 'checkout/cart';
   
    
    return $this->resultRedirectFactory->create()->setUrl($checkoutUrl);
}else {
				$this->messageManager->addErrorMessage(__("Error in Payment for your Seller Verification # %1.",$vendorsVerificationIncId));
				//return $this->_redirect ('checkout/cart/index/' );
               $cartUrl = $this->storeManager
    ->getStore()
    ->getUrl('checkout/cart', ['_scope_to_url' => true]);

return $this->resultRedirectFactory
    ->create()
    ->setUrl($cartUrl);
			} 
			
		} catch(\Exception $e){
		    $this->messageManager->addErrorMessage($e->getMessage());		    
		    return $this->_redirect('vendors/vendorverification/verification/index/');
		}
	}
	
	private function isQuoteItemExist($verificationId)
{
    $quote = $this->cart->getQuote();

    foreach ($quote->getAllVisibleItems() as $item) {

        // Same verification already in cart
        if ((int)$item->getVendorVerificationId() === (int)$verificationId) {
            return true;
        }

        // Safety: same SKU already exists
        if ($item->getSku() === self::VERIFICATION_FEES_PRODUCT_SKU) {
            return true;
        }
    }

    return false;
}

	
// 	private function addVerificationFeestoQuote($verificationId)
// {
//     $vendorsVerification = $this->vendorsVerificationFactory
//         ->create()
//         ->load($verificationId);

//     $price = (float) $vendorsVerification->getAmount();

//     $product = $this->productRepository->get(
//         self::VERIFICATION_FEES_PRODUCT_SKU
//     );

//     // Product acts only as carrier
//     $product->setPrice(0);
//     $product->setFinalPrice(0);
//     $product->setIsSuperMode(true);

//     $additionalOptions = [
//         [
//             'label' => __('Verification Fees'),
//             'value' => __('Pay SAR %1 Verification Fees', number_format($price, 2))
//         ],
//         [
//             'label' => __('Verification ID'),
//             'value' => $vendorsVerification->getIncId()
//         ]
//     ];

//     $params = [
//         'product' => $product->getId(),
//         'qty' => 1,
//         'options' => [
//             'additional_options' => $this->serializer->serialize($additionalOptions)
//         ]
//     ];

//     $this->cart->addProduct($product, $params);

//     $quoteItem = $this->cart->getQuote()->getItemByProduct($product);

//     $quoteItem->setCustomPrice($price);
//     $quoteItem->setOriginalCustomPrice($price);
//     $quoteItem->getProduct()->setIsSuperMode(true);

//     $quoteItem->setVendorVerificationId($vendorsVerification->getId());

//     $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
//     $this->cart->save();

//     return true;
// }
private function addVerificationFeestoQuote($verificationId)
{
     $this->cleanupDuplicateVerificationItems();

    $vendorsVerification = $this->vendorsVerificationFactory
        ->create()
        ->load($verificationId);

    $price = (float) $vendorsVerification->getAmount();

    $product = $this->productRepository->get(
        self::VERIFICATION_FEES_PRODUCT_SKU
    );

    $additionalOptions = [
        [
            'label' => __('Verification Fees'),
            'value' => __('Pay SAR %1 Verification Fees', number_format($price, 2))
        ],
        [
            'label' => __('Verification ID'),
            'value' => $vendorsVerification->getIncId()
        ]
    ];

    $quote = $this->cart->getQuote();
    $quoteItem = $this->getVerificationQuoteItem();

    // 🔁 CASE 1: Item already exists → UPDATE ONLY
    if ($quoteItem) {

        // overwrite verification id
        $quoteItem->setVendorVerificationId($vendorsVerification->getId());

        // overwrite price
        $quoteItem->setCustomPrice($price);
        $quoteItem->setOriginalCustomPrice($price);
        $quoteItem->setQty(1); // 🔒
        $quoteItem->setIsQtyDecimal(false);
        $quoteItem->getProduct()->setIsSuperMode(true);

        // overwrite options (IMPORTANT)
        
        // $quoteItem->addOption([
        //     'code' => 'additional_options',
        //     'value' => $this->serializer->serialize($additionalOptions)
        // ]);  
        $option = $quoteItem->getOptionByCode('additional_options');

if ($option) {
    $option->setValue($this->serializer->serialize($additionalOptions));
} else {
    $quoteItem->addOption([
        'code' => 'additional_options',
        'value' => $this->serializer->serialize($additionalOptions)
    ]);
}


    } 
    // ➕ CASE 2: Item not exists → ADD
    else {

        $product->setPrice(0);
        $product->setFinalPrice(0);
        $product->setIsSuperMode(true);

        $params = [
            'product' => $product->getId(),
            'qty' => 1
        ];

        $this->cart->addProduct($product, $params);

        $quoteItem = $this->getVerificationQuoteItem();

        $quoteItem->setVendorVerificationId($vendorsVerification->getId());
        $quoteItem->setCustomPrice($price);
        $quoteItem->setOriginalCustomPrice($price);
        $quoteItem->getProduct()->setIsSuperMode(true);
       
        // $quoteItem->addOption([
        //     'code' => 'additional_options',
        //     'value' => $this->serializer->serialize($additionalOptions)
        // ]);
        $option = $quoteItem->getOptionByCode('additional_options');

if ($option) {
    $option->setValue($this->serializer->serialize($additionalOptions));
} else {
    $quoteItem->addOption([
        'code' => 'additional_options',
        'value' => $this->serializer->serialize($additionalOptions)
    ]);
}

    }

    $quote->setTotalsCollectedFlag(false)->collectTotals();
    $this->cart->save();

    return true;
}

private function getVerificationQuoteItem()
{
    $quote = $this->cart->getQuote();

    foreach ($quote->getAllVisibleItems() as $item) {
        if ($item->getSku() === self::VERIFICATION_FEES_PRODUCT_SKU) {
            return $item;
        }
    }
    return null;
}

private function getVerificationFeesProdIdbySku($sku){
		return $this->productRepository->get($sku)->getId();
	}
/**
 * 🔒 Ensure only ONE verification product in cart
 * Extra verification items auto remove
 */
private function cleanupDuplicateVerificationItems()
{
    $quote = $this->cart->getQuote();
    $found = false;

    foreach ($quote->getAllVisibleItems() as $item) {
        if ($item->getSku() === self::VERIFICATION_FEES_PRODUCT_SKU) {
            if ($found) {
                // ❌ extra duplicate → remove
                $quote->removeItem($item->getId());
            } else {
                // ✅ first item allowed
                $found = true;
            }
        }
    }

    if ($found) {
        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
    }
}

}