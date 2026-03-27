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
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
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
        $this->_logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please log in.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $post = $this->getRequest()->getPostValue();
        $customer = $this->customerSession->getCustomer();
        $currentCustomerId = $customer->getId();
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

                // Prevent customer requesting quote for own product
                if ($currentVendorId > 0 && (int)$currentVendorId === (int)$productVendorId) {
                    $this->messageManager->addErrorMessage(__('You cannot request a quote for your own product: %1', $product->getName()));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }

                $vendorWiseProducts[$productVendorId][] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'name' => $product->getName(),
                    'customer_is_vendor' => ((int)$currentCustomerId === (int)$productVendorId)
                ];
            }

            $lastQuoteId = null;
            $now = $this->_localeDate->date()->format('Y-m-d H:i:s');

            foreach ($vendorWiseProducts as $vId => $items) {
                // Save Quote
                $quoteModel = $this->quoteFactory->create();
                $quoteModel->setData([
                    'customer_id' => $currentCustomerId,
                    'vendor_id' => $vId,
                    'status' => 'pending',
                    'country_id' => $post['country_id'] ?? '',
                    'region_id' => $post['region_id'] ?? 0,
                    'customer_note' => $post['customer_note'] ?? ''
                ]);
                $quoteModel->save();
                $newQuoteId = $quoteModel->getId();
                $lastQuoteId = $newQuoteId;

                // Save Items
                foreach ($items as $itemData) {
                    $itemModel = $this->itemFactory->create();
                    $itemModel->setData([
                        'quote_id' => $newQuoteId,
                        'product_id' => $itemData['product_id'],
                        'qty' => $itemData['qty']
                    ]);
                    $itemModel->save();
                }

                // Save to Vnecoms Inbox
                $subject = 'New Quote Request #' . $newQuoteId;

                
                $customerIsVendor = $itemData['customer_is_vendor'] ?? false;
                $this->saveToVnecomsInbox($currentCustomerId, $vId, $subject, $post['customer_note'] ?? '', $customerIsVendor);
                

                // Send Vendor Email
                try {
                    $vendor = $this->vendorFactory->create()->load($vId);
                    $this->sendEmail('rfq_vendor_email_template', $vendor->getEmail(), $vendor->getName(), [
                        'quote_id' => $newQuoteId,
                        'customer_name' => $customerName
                    ]);
                } catch (\Exception $e) {
                    $this->_logger->critical('Vendor Email Error: ' . $e->getMessage());
                }
            }

            // Send Customer & Admin Email (skip customer if customer is vendor)
            try {
                $adminEmail = $this->scopeConfig->getValue('trans_email/ident_general/email');
                $adminName = $this->scopeConfig->getValue('trans_email/ident_general/name');

                $this->sendEmail('rfq_customer_email_template', $customer->getEmail(), $customerName, ['quote_id' => $lastQuoteId, 'customer_name' => $customerName]);
                $this->sendEmail('rfq_admin_email_template', $adminEmail, $adminName, ['quote_id' => $lastQuoteId, 'customer_name' => $customerName]);
            } catch (\Exception $e) {
                $this->messageManager->addWarningMessage(__('Quote saved, but confirmation email could not be sent.'));
            }

            $this->catalogSession->setQuoteItems([]);
            $this->messageManager->addSuccessMessage(__('Your quotation requests have been sent successfully and added to your inbox!'));

            return $this->resultRedirectFactory->create()->setPath('*/*/success');

        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage() . "\n" . $e->getTraceAsString());
            $this->messageManager->addErrorMessage(__('Something went wrong: %1', $e->getMessage()));
            return $this->resultRedirectFactory->create()->setRefererUrl();
        }
    }

    /**
     * Save message to Vnecoms Inbox
     *
     * @param int $customerId
     * @param int $vendorId
     * @param string $subject
     * @param string $content
     * @param bool $customerIsVendor
     * @return bool
     */
public function saveToVnecomsInbox($customerId, $vendorId, $subject, $content)
{
    $resource = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\ResourceConnection::class);
    $connection = $resource->getConnection();
    
    $customerTable = $resource->getTableName('customer_entity');
    $messageTable = $resource->getTableName('ves_vendor_message');
    $detailTable = $resource->getTableName('ves_vendor_message_detail');

    try {
        // 1. Get Vendor Email using Factory (Simple & Clean)
        $vendor = $this->vendorFactory->create()->load($vendorId);
        $vendorEmail = $vendor->getEmail();

        if (!$vendorEmail) {
            $this->_logger->error("Vendor Email not found for ID: $vendorId");
            return false;
        }

        // 2. Get Real Owner ID (Customer ID linked to Vendor Email)
        $realOwnerId = $connection->fetchOne("SELECT entity_id FROM $customerTable WHERE email = ?", [$vendorEmail]);
        
        if (!$realOwnerId) {
            $this->_logger->error("No customer account found for vendor email: $vendorEmail");
            return false;
        }

        // 3. Get Names for Detail Table
        $senderData = $connection->fetchRow("SELECT email, firstname, lastname FROM $customerTable WHERE entity_id = ?", [$customerId]);
        $receiverData = $connection->fetchRow("SELECT firstname, lastname FROM $customerTable WHERE entity_id = ?", [$realOwnerId]);

        $identifier = md5($customerId . $vendorId . microtime() . uniqid());
        $now = date('Y-m-d H:i:s');

        // --- STEP A: FOR VENDOR (INBOX) ---
        $connection->insert($messageTable, [
            'identifier' => $identifier,
            'owner_id'   => $realOwnerId, 
            'status'     => 1, // Inbox Status
            'is_inbox'   => 1,
            'is_outbox'  => 0,
            'is_deleted' => 0,
            'created_at' => $now
        ]);
        $vMsgId = $connection->lastInsertId();

        $connection->insert($detailTable, [
            'message_id'     => $vMsgId,
            'sender_id'      => $customerId,
            'sender_email'   => $senderData['email'] ?? '',
            'sender_name'    => ($senderData['firstname'] ?? '') . ' ' . ($senderData['lastname'] ?? ''),
            'receiver_id'    => $realOwnerId,
            'receiver_email' => $vendorEmail,
            'receiver_name'  => ($receiverData['firstname'] ?? '') . ' ' . ($receiverData['lastname'] ?? ''),
            'subject'        => $subject,
            'content'        => $content,
            'is_read'        => 0,
            'created_at'     => $now
        ]);

        // --- STEP B: FOR CUSTOMER (OUTBOX) ---
        $connection->insert($messageTable, [
            'identifier' => $identifier,
            'owner_id'   => $customerId, 
            'status'     => 2, // Outbox Status
            'is_inbox'   => 0,
            'is_outbox'  => 1,
            'is_deleted' => 0,
            'created_at' => $now
        ]);
        $cMsgId = $connection->lastInsertId();

        $connection->insert($detailTable, [
            'message_id'     => $cMsgId,
            'sender_id'      => $customerId,
            'sender_email'   => $senderData['email'] ?? '',
            'sender_name'    => ($senderData['firstname'] ?? '') . ' ' . ($senderData['lastname'] ?? ''),
            'receiver_id'    => $realOwnerId,
            'receiver_email' => $vendorEmail,
            'receiver_name'  => ($receiverData['firstname'] ?? '') . ' ' . ($receiverData['lastname'] ?? ''),
            'subject'        => $subject,
            'content'        => $content,
            'is_read'        => 1,
            'created_at'     => $now
        ]);

        return true;
    } catch (\Exception $e) {
        $this->_logger->error($e->getMessage());
        return false;
    }
}

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