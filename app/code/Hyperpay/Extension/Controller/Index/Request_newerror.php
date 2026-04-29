<?php

namespace Hyperpay\Extension\Controller\Index;

class Request extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Hyperpay\Extension\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Hyperpay\Extension\Model\Adapter
     */
    protected $_adapter;
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remote;
    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface
     */
    protected $_stockManagement;
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_resolver;
    protected $_quoteFactory;
    /**
     * @var string
     */
    protected $_storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context                  $context,
        \Magento\Framework\Registry                            $coreRegistry,
        \Hyperpay\Extension\Helper\Data                        $helper,
        \Magento\Checkout\Model\Session                        $checkoutSession,
        \Magento\Framework\View\Result\PageFactory             $pageFactory,
        \Magento\Store\Model\StoreManagerInterface             $storeManager,
        \Magento\Framework\Locale\Resolver                     $resolver,
        \Hyperpay\Extension\Model\Adapter                      $adapter,
        \Magento\Quote\Model\QuoteFactory                      $quoteFactory,
        \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress   $remote
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->_adapter = $adapter;
        $this->_storeManager = $storeManager;
        $this->_resolver = $resolver;
        $this->_remote = $remote;
        $this->_stockManagement = $stockManagement;
        $this->_quoteFactory = $quoteFactory;
    }

    public function execute()
    {
        try {
            if (!($this->_checkoutSession->getLastRealOrderId())) {
                $this->_helper->doError(__('Order is not found'));
            }

            $order = $this->_checkoutSession->getLastRealOrder();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_pageFactory->create();
        }

        $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
        $quote->setIsActive(true);
        $quote->save();
        $this->_checkoutSession->replaceQuote($quote);

        if (($order->getState() !== 'new') && ($order->getState() !== 'pending_payment')) {
            $this->messageManager->addError(__("This order has already been processed, Please place a new order"));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }

        try {
            $base = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $statusUrl = $base . "hyperpay/index/status/?method=" . $order->getPayment()->getData('method');
            
            // Returns Array: ['script_url' => ..., 'integrity' => ...]
            $checkoutData = $this->prepareTheCheckout($order, $statusUrl);

            // ✅ FIXED: Separately registering values to avoid Unserialize errors
            $this->_coreRegistry->register('formurl', $checkoutData['script_url']);
            $this->_coreRegistry->register('integrity_hash', $checkoutData['integrity']);
            $this->_coreRegistry->register('status', $statusUrl);

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }

        return $this->_pageFactory->create();
    }

    /**
     * Build data and make a request to hyperpay payment gateway
     */
    public function prepareTheCheckout($order, $status)
    {
        $payment = $order->getPayment();
        $method = $payment->getData('method');
        $email = $order->getBillingAddress()->getEmail();
        $orderId = $order->getIncrementId();

        $amount = $order->getBaseGrandTotal();
        $total = $this->_helper->convertPrice($payment, $amount);

        $grandTotal = $this->_adapter->getEnv() 
            ? (int)$total 
            : number_format($total, 2, '.', '');

        $currency = $this->_adapter->getSupportedCurrencyCode($method);
        $paymentType = $this->_adapter->getPaymentType($method);
        $entityId = $this->_adapter->getEntity($method);
        
        $baseUrl = rtrim($this->_adapter->getUrl(), '/') . '/';
        $url = $baseUrl . 'v1/checkouts';

        $params = [
            'entityId'              => $entityId,
            'amount'                => $grandTotal,
            'currency'              => $currency,
            'paymentType'           => $paymentType,
            'notificationUrl'       => $status,
            'customer.email'        => $email,
            'merchantTransactionId' => $orderId,
            'testMode'              => 'EXTERNAL',
            'integrity'             => 'true',
            'customParameters[plugin]' => 'magento',
            'customParameters[3DS2_enrolled]' => 'true',
            'customParameters[3DS2_flow]'     => 'challenge'
        ];

        if ($method == 'HyperPay_stc') {
            $params['customParameters[branch_id]'] = '1';
            $params['customParameters[teller_id]'] = '1';
            $params['customParameters[device_id]'] = '1';
            $params['customParameters[bill_number]'] = $orderId;
        }

        $data = http_build_query($params);

        $addressData = $this->_helper->getBillingAndShippingAddress($order);
        if (!empty($addressData)) {
            $data .= '&' . ltrim($addressData, '&');
        }

        $accessToken = $this->_adapter->getAccessToken();
        $this->_helper->setHeaders(['Authorization' => 'Bearer ' . $accessToken]);

        $decodedData = $this->_helper->getCurlReqData($url, $data);

        if (!isset($decodedData['id'])) {
            $resCode = $decodedData['result']['code'] ?? 'N/A';
            $resDesc = $decodedData['result']['description'] ?? 'No description';
            throw new \Exception("HyperPay Error: [$resCode] $resDesc");
        }

        $checkoutId = $decodedData['id'];
        $integrityHash = $decodedData['integrity'] ?? '';

        return [
            'script_url' => $baseUrl . "v1/paymentWidgets.js?checkoutId=" . $checkoutId,
            'integrity'  => $integrityHash,
            'checkoutId' => $checkoutId
        ];
    }

    private function checkIfExist($order, $entityId, $auth, $id, $baseUrl)
    {
        $url = $baseUrl . "query";
        $url .= "?entityId=" . $entityId;
        $url .= "&merchantTransactionId=" . $id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:Bearer ' . $auth));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($responseData);
        if (isset($response->result->code) && $response->result->code === "700.400.580") {
            return false;
        }
        if (!isset($response->payments) || count($response->payments) == 0) {
            return false;
        }
        $orderTime = new \DateTime($order->getCreatedAt());
        foreach ($response->payments as $payment) {
            $paymentTime = new \DateTime($payment->timestamp);
            $interval = date_diff($paymentTime, $orderTime);
            $diffDays = $interval->format('%a');
            if ($diffDays <= 1) {
                return true;
            }
        }
        return false;
    }
}