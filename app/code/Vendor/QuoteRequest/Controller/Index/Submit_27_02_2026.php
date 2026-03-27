<?php
namespace Vendor\QuoteRequest\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session as CatalogSession;

class Submit extends Action
{
    protected $customerSession;
    protected $catalogSession;
    protected $productRepository;
    protected $quoteFactory;
    protected $itemFactory;
    protected $_vendorSession; // Vnecoms Session

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CatalogSession $catalogSession,
        ProductRepositoryInterface $productRepository,
        \Vendor\QuoteRequest\Model\QuoteFactory $quoteFactory,
        \Vendor\QuoteRequest\Model\ItemFactory $itemFactory,
        \Vnecoms\Vendors\Model\Session $vendorSession // Inject Vnecoms Session
    ) {
        $this->customerSession = $customerSession;
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->itemFactory = $itemFactory;
        $this->_vendorSession = $vendorSession;
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

        // Check if the current user is actually logged in as a Vendor
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

                // VALIDATION: Sirf tab rokein jab user active Vendor ho aur product uska ho
                if ($currentVendorId > 0 && (int)$currentVendorId === (int)$productVendorId) {
                    $this->messageManager->addErrorMessage(
                        __('You cannot request a quote for your own product: %1', $product->getName())
                    );
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }

                $vendorWiseProducts[$productVendorId][] = [
                    'product_id' => $productId,
                    'qty' => $qty
                ];
            }

            // Save entries...
            foreach ($vendorWiseProducts as $vId => $items) {
                $quoteModel = $this->quoteFactory->create();
                $quoteModel->setData([
                    'customer_id'   => $currentCustomerId,
                    'vendor_id'     => $vId,
                    'status'        => 'pending',
                    'country_id'    => $post['country_id'],
                    'region_id'     => $post['region_id'] ?? 0,
                    'customer_note' => $post['customer_note']
                ]);
                $quoteModel->save();
                
                $newQuoteId = $quoteModel->getId();
                foreach ($items as $itemData) {
                    $itemModel = $this->itemFactory->create();
                    $itemModel->setData([
                        'quote_id'   => $newQuoteId,
                        'product_id' => $itemData['product_id'],
                        'qty'        => $itemData['qty']
                    ]);
                    $itemModel->save();
                }
            }

            $this->catalogSession->setQuoteItems([]); 
            $this->messageManager->addSuccessMessage(__('Your quotation requests have been sent successfully!'));
            return $this->resultRedirectFactory->create()->setPath('*/*/success');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
    }
}