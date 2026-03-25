<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Vnecoms\Vendors\Model\VendorFactory;
use Vnecoms\VendorsMessage\Model\MessageFactory;
use Vnecoms\VendorsMessage\Model\Message\DetailFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Submit extends Action
{
    protected $customerSession;
    protected $catalogSession;
    protected $productRepository;
    protected $quoteFactory;
    protected $itemFactory;
    protected $_vendorSession;
    protected $transportBuilder;
    protected $storeManager;
    protected $vendorFactory;
    protected $messageFactory;
    protected $detailFactory;
    protected $_logger;
    protected $_localeDate;
    protected $customerRepository;
   protected $scopeConfig;

    public function __construct(

        Context $context,
        CustomerSession $customerSession,
        CatalogSession $catalogSession,
        ProductRepositoryInterface $productRepository,
        \Vendor\QuoteRequest\Model\QuoteFactory $quoteFactory,
        \Vendor\QuoteRequest\Model\ItemFactory $itemFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        VendorFactory $vendorFactory,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        MessageFactory $messageFactory,
         DetailFactory $detailFactory,
         TimezoneInterface $localeDate,
         CustomerRepositoryInterface $customerRepository,
         ScopeConfigInterface $scopeConfig,
         \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->itemFactory = $itemFactory;
        $this->_vendorSession = $vendorSession;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        $this->messageFactory = $messageFactory;
        $this->detailFactory = $detailFactory;
        $this->_localeDate = $localeDate;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please log in.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $post = $this->getRequest()->getPostValue();
        $currentCustomerId = $this->customerSession->getCustomerId();
        $customer = $this->customerSession->getCustomer();
        $customerName = $customer->getFirstname();

        $currentVendorId = 0;
        if ($this->_vendorSession->isLoggedIn()) {
            $currentVendorId = $this->_vendorSession->getVendorId();
        }

        try {
            $quantities = $post['qty'] ?? [];
            $vendorWiseProducts = [];

            foreach ($quantities as $productId => $qty) {
                $product = $this->productRepository->getById($productId);
                $productVendorId = $product->getVendorId(); 

                if ($currentVendorId > 0 && (int)$currentVendorId === (int)$productVendorId) {
                    $this->messageManager->addErrorMessage(__('You cannot request a quote for your own product: %1', $product->getName()));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }

                $vendorWiseProducts[$productVendorId][] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'name' => $product->getName()
                ];
            }

            $lastQuoteId = null;

            foreach ($vendorWiseProducts as $vId => $items) {
                // 1. Save Quote

                $quoteModel = $this->quoteFactory->create();
                $quoteModel->setData([
                    'customer_id'   => $currentCustomerId,
                    'vendor_id'     => $vId,
                    'status'        => 'pending',
                   'country_id'    => $post['country_id'] ?? '',
                'region_id'     => $post['region_id'] ?? 0,
                'customer_note' => $post['customer_note'] ?? ''
                ]);
                $quoteModel->save();
                $newQuoteId = $quoteModel->getId();
                $lastQuoteId = $newQuoteId;

                // 2. Save Items
                foreach ($items as $itemData) {
                    $itemModel = $this->itemFactory->create();
                    $itemModel->setData([
                        'quote_id'   => $newQuoteId,
                        'product_id' => $itemData['product_id'],
                        'qty'        => $itemData['qty']
                    ]);
                    $itemModel->save();
                }
                // --- NEW CODE START: Generate Product List for Email ---
                $productsHtml = '<table style="width:100%; border:1px solid #e0e0e0; border-collapse:collapse; font-family:Arial,sans-serif;">';
                $productsHtml .= '<thead><tr style="background:#f5f5f5;"><th style="padding:10px; border:1px solid #e0e0e0; text-align:left;">Product</th><th style="padding:10px; border:1px solid #e0e0e0; text-align:center;">Quantity</th></tr></thead>';
                $productsHtml .= '<tbody>';
                foreach ($items as $item) {
                    $productsHtml .= '<tr>';
                    $productsHtml .= '<td style="padding:10px; border:1px solid #e0e0e0;">' . htmlspecialchars($item['name']) . '</td>';
                    $productsHtml .= '<td style="padding:10px; border:1px solid #e0e0e0; text-align:center;">' . (int)$item['qty'] . '</td>';
                    $productsHtml .= '</tr>';
                }
                $productsHtml .= '</tbody></table>';
                // --- NEW CODE END ---
                // 3. Save to Vnecoms Inbox
               // $this->saveToVnecomsInbox($vId, $currentCustomerId, $newQuoteId, $post['customer_note']);
                $subject = 'New Quote Request #' . $newQuoteId;

$this->saveToVnecomsInbox(
    $currentCustomerId,
    $vId,
    $subject,
    $post['customer_note']
);
                // 4. Send Vendor Email (Individual Try-Catch to prevent crash)
                try {
                    $vendor = $this->vendorFactory->create()->load($vId);
                    $this->sendEmail('rfq_vendor_email_template', $vendor->getEmail(), $vendor->getName(), [
                        'quote_id' => $newQuoteId,
                        'customer_name' => $customerName,
                        'products_list' => $productsHtml
                    ]);
                } catch (\Exception $e) {
                    // Silent fail for vendor email
                }
            }

            // 5. Send Customer & Admin Email (Individual Try-Catch)
            try {
                $this->sendEmail('rfq_customer_email_template', $customer->getEmail(), $customerName, ['quote_id' => $lastQuoteId, 'customer_name' => $customerName]);
                //admin email idfetch for admin email id from config
                $adminEmail = $this->scopeConfig->getValue(
                  'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $adminName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
                
            $this->sendEmail(
                'rfq_admin_email_template',
                 $adminEmail,
                 $adminName,
                [
                'quote_id' => $lastQuoteId,
                'customer_name' => $customerName,
                'products_list' => $productsHtml,
                'customer message' => $post['customer_note'] ?? ''
                ]
            );
                //$this->sendEmail('rfq_admin_email_template', $adminEmail, $adminName, ['quote_id' => $lastQuoteId, 'customer_name' => $customerName]);
            } catch (\Exception $e) {
                //$this->messageManager->addWarningMessage(__('Quote saved, but confirmation email could not be sent.'));
            }

            $this->catalogSession->setQuoteItems([]); 
            $this->messageManager->addSuccessMessage(__('Your quotation requests have been sent successfully and added to your inbox!'));
            
            return $this->resultRedirectFactory->create()->setPath('*/*/success');

        } catch (\Exception $e) {
           $this->_logger->critical($e->getMessage()); // Yahan log add karein
    $this->messageManager->addErrorMessage($e->getMessage());
    
    // Yahan check karein ki agar index page nahi hai toh referer par bhejien
    return $this->resultRedirectFactory->create()->setRefererUrl();
        }
    }


public function saveToVnecomsInbox($customerId, $vendorId, $subject, $content)
{
    try {
        $now = date('Y-m-d H:i:s');

        // --- STEP 1: Load Customer (Sender) ---
        $customer = $this->customerRepository->getById($customerId);
        $senderData = [
            'id'        => $customer->getId(),
            'email'     => $customer->getEmail(),
            'firstname' => $customer->getFirstname(),
            'lastname'  => $customer->getLastname()
        ];

        // --- STEP 2: Load Vendor (Receiver) ---
        $vendor = $this->vendorFactory->create()->load($vendorId);
        $vendorEmail = $vendor->getEmail();
        $vendorName  = $vendor->getName();

        if (!$vendorEmail) {
            $this->_logger->error("Vendor email not found for ID: $vendorId");
            return false;
        }

        // --- STEP 3: Find Real Owner ID (Customer account linked to Vendor email) ---
        try {
            $customerByVendorEmail = $this->customerRepository->get($vendorEmail);
            $realOwnerId = $customerByVendorEmail->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_logger->error("No customer account found for vendor email: $vendorEmail");
            return false;
        }

        $receiverData = [
            'id'        => $realOwnerId,
            'firstname' => $customerByVendorEmail->getFirstname(),
            'lastname'  => $customerByVendorEmail->getLastname(),
            'email'     => $vendorEmail
        ];

        // --- STEP 4: Prepare Identifier ---
        $identifier = md5($customerId . $vendorId . microtime() . uniqid());

        $resource = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        $messageTable = $resource->getTableName('ves_vendor_message');
        $detailTable  = $resource->getTableName('ves_vendor_message_detail');

        // --- STEP 5: Insert Vendor Inbox Message ---
        $connection->insert($messageTable, [
            'identifier' => $identifier,
            'owner_id'   => $realOwnerId,
            'status'     => 1, // Inbox
            'is_inbox'   => 1,
            'is_outbox'  => 0,
            'is_deleted' => 0,
            'created_at' => $now
        ]);
        $vMsgId = $connection->lastInsertId();

        $connection->insert($detailTable, [
            'message_id'     => $vMsgId,
            'sender_id'      => $senderData['id'],
            'sender_email'   => $senderData['email'],
            'sender_name'    => $senderData['firstname'] . ' ' . $senderData['lastname'],
            'receiver_id'    => $receiverData['id'],
            'receiver_email' => $receiverData['email'],
            'receiver_name'  => $receiverData['firstname'] . ' ' . $receiverData['lastname'],
            'subject'        => $subject,
            'content'        => $content,
            'is_read'        => 0, // unread for vendor
            'created_at'     => $now
        ]);

        // --- STEP 6: Insert Customer Outbox Message ---
        $connection->insert($messageTable, [
            'identifier' => $identifier,
            'owner_id'   => $customerId,
            'status'     => 2, // Outbox
            'is_inbox'   => 0,
            'is_outbox'  => 1,
            'is_deleted' => 0,
            'created_at' => $now
        ]);
        $cMsgId = $connection->lastInsertId();

        $connection->insert($detailTable, [
            'message_id'     => $cMsgId,
            'sender_id'      => $senderData['id'],
            'sender_email'   => $senderData['email'],
            'sender_name'    => $senderData['firstname'] . ' ' . $senderData['lastname'],
            'receiver_id'    => $receiverData['id'],
            'receiver_email' => $receiverData['email'],
            'receiver_name'  => $receiverData['firstname'] . ' ' . $receiverData['lastname'],
            'subject'        => $subject,
            'content'        => $content,
            'is_read'        => 1, // read for customer
            'created_at'     => $now
        ]);

        return true;

    } catch (\Exception $e) {
        $this->_logger->error("saveToVnecomsInbox Error: " . $e->getMessage());
        return false;
    }
}
// protected function saveToVnecomsInbox($vendorId, $customerId, $quoteId, $note)
// {
//     $identifier = md5(microtime() . $quoteId);
//     $now = date('Y-m-d H:i:s');
//     $subject = __('New Quote Request #%1', $quoteId);
//     $content = $note ?: __('I would like to request a quote for your products.');

//     try {
//         // 1. Parent Message Entry (Vendor Side)
//         $message = $this->messageFactory->create();
//         $message->setData([
//             'identifier' => $identifier,
//             'owner_id'   => $vendorId,
//             'status'     => 1,
//             'is_inbox'   => 1,
//             'is_outbox'  => 0,
//             'is_deleted' => 0,
//             'created_at' => $now
//         ]);
//         $message->save();
//         $msgId = $message->getId();

//         // 2. Message Detail Entry (Yeh wahi table hai jo aapne abhi dikhayi)
//         $resource = $this->quoteFactory->create()->getResource();
//         $connection = $resource->getConnection();
//         $detailTable = $resource->getTable('ves_vendor_message_detail');
        
//         // Customer ki details fetch karein (Optional: for sender_name/email)
//        $customer = $this->customerSession->getCustomer();
//        $vendor = $this->vendorFactory->create()->load($vendorId);

//     $detailData = [
//     'message_id'     => $msgId,
//     'sender_id'      =>$customer->getId(),   // customer id
//     'sender_email'   => $customer->getEmail(),
//     'sender_name'    => $customer->getName(),
//     'receiver_id'    => $vendorId,
//     'receiver_email' => $vendor->getEmail(),
//     'receiver_name'  => $vendor->getName(),
//     'subject'        => 'New Quote Request #' . $quoteId,
//     'content'        => $content,
//     'is_read'        => 0,
//     'created_at'     => $now
// ];

// try {
//     $connection->insert($detailTable, $detailData);
// } catch (\Exception $e) {
//     echo $e->getMessage();
//    // die();
// }
//     } catch (\Exception $e) {
//         $this->_logger->critical("Vnecoms Detail Table Error: " . $e->getMessage());
//     }
// }

    public function sendEmail($templateId, $toEmail, $toName, $templateVars)
    {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($templateVars)
            ->setFrom(['name' => 'Admin', 'email' => 'admin@example.com'])
            ->addTo($toEmail, $toName)
            ->getTransport();

        $transport->sendMessage();
    }
}