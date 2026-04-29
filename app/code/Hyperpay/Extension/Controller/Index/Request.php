<?php

namespace Hyperpay\Extension\Controller\Index;


class Request extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     *
     * @var \Hyperpay\Extension\Helper\Data
     */
    protected $_helper;
    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     *
     * @var \Hyperpay\Extension\Model\Adapter
     */
    protected $_adapter;
    /**
     *
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remote;
    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface
     */
    protected $_stockManagement;
    /**
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_resolver;
    protected $_quoteFactory;
    /**
     *
     * @var string
     */
    protected $_storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Hyperpay\Extension\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement
     * @param \Hyperpay\Extension\Model\Adapter $adapter
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remote
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
            $this->messageManager->addError(__("This order has already been processed,Please place a new order"));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }
        try {
            $base = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $statusUrl = $base . "hyperpay/index/status/?method=" . $order->getPayment()->getData('method');
            $urlReq = $this->prepareTheCheckout($order, $statusUrl);

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/failure');
            return $resultRedirect;
        }

        $this->_coreRegistry->register('formurl', $urlReq);
        $this->_coreRegistry->register('status', $statusUrl);

        return $this->_pageFactory->create();
    }

    /**
     * Build data and make a request to hyperpay payment gateway
     * and return url of form
     *
     * @param $order
     * @return string
     */
  public function prepareTheCheckout($order, $status)
{
    $payment   = $order->getPayment();
    $method    = $payment->getData('method');
    $email     = $order->getBillingAddress()->getEmail();
    $orderId   = $order->getIncrementId();
    $amount    = $order->getBaseGrandTotal();
    $total     = $this->_helper->convertPrice($payment, $amount);

    // Format amount depending on environment
    if ($this->_adapter->getEnv()) {
        $grandTotal = (int)$total;
    } else {
        $grandTotal = number_format($total, 2, '.', '');
    }

    $currency     = $this->_adapter->getSupportedCurrencyCode($method);
    $paymentType  = $this->_adapter->getPaymentType($method);
    $entityId     = $this->_adapter->getEntity($method);
    $baseUrl      = rtrim($this->_adapter->getUrl(), '/'); // ensure trailing slash
    $url          = $baseUrl . '/v1/checkouts';

    // Build parameters as array (safer than string concatenation)
    $params = [
        'entityId'              => $entityId,
        'notificationUrl'       => $status,
        'amount'                => $grandTotal,
        'currency'              => $currency,
        'paymentType'           => $paymentType,
        'customer.email'        => $email,
        'integrity'             => 'true',
        'customParameters[plugin]' => 'magento',
        'merchantTransactionId' => $orderId,
    ];

    // Add optional risk parameters
    if (!empty($this->_adapter->getRiskChannelId())) {
        $params['risk.channelId'] = $this->_adapter->getRiskChannelId();
        $params['risk.serviceId'] = 'I';
        $params['risk.amount']    = $grandTotal;
        $params['risk.parameters[USER_DATA1]'] = 'Mobile';
    }

    // Method‑specific parameters
    if ($method === 'HyperPay_SadadNcb') {
        $params['bankAccount.country'] = 'SA';
    }
    if ($method === 'HyperPay_stc') {
        $params['customParameters[branch_id]']  = '1';
        $params['customParameters[teller_id]']  = '1';
        $params['customParameters[device_id]']  = '1';
        $params['customParameters[locale]']     = substr($this->_resolver->getLocale(), 0, -3);
        $params['customParameters[bill_number]'] = $orderId;
    }
    if ($method === 'HyperPay_Click_to_pay') {
        $params['customParameters[3DS2_enrolled]'] = 'true';
    }
    if ($this->_adapter->getEnv() && $method === 'HyperPay_ApplePay') {
        $params['customParameters[3Dsimulator.forceEnrolled]'] = 'true';
    }

    // Add billing/shipping addresses
    $addressParams = $this->_helper->getBillingAndShippingAddress($order);
    if (!empty($addressParams)) {
        $params = array_merge($params, $addressParams);
    }

    // Prepare headers
    $accesstoken = $this->_adapter->getAccessToken();
    $headers     = ['Authorization: Bearer ' . $accesstoken];

    echo "<pre>";
print_r($params);

    // Execute cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // set true in production
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $responseData = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new \Exception(curl_error($ch));
    }
    curl_close($ch);

    $decodedData = json_decode($responseData, true);

// DEBUG LOG (IMPORTANT)
echo "<pre>";
echo "RAW RESPONSE:\n";
print_r($responseData);

echo "\n\nDECODED RESPONSE:\n";
print_r($decodedData);
exit();
if (empty($decodedData['id'])) {

    $code = $decodedData['result']['code'] ?? 'NO_CODE';
    $desc = $decodedData['result']['description'] ?? $responseData;

    throw new \Exception("HyperPay Error [$code]: $desc");
}

    return $baseUrl . '/paymentWidgets.js?checkoutId=' . $decodedData['id'];
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($responseData);
        if ($response->result->code === "700.400.580") {
            return false;
        }
        if (count($response->payments) == 0) {
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
