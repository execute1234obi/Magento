<?php
namespace Vendor\QuoteRequest\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Catalog\Api\ProductRepositoryInterface;

class View extends Template
{
    protected $catalogSession;
    protected $productRepository;
    protected $_countryFactory;
    protected $_directoryHelper;

    public function __construct(
        Template\Context $context,
        CatalogSession $catalogSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->catalogSession = $catalogSession;
        $this->productRepository = $productRepository;
        $this->_countryFactory = $countryFactory;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    public function getCountryCollection() {
            return $this->_countryFactory->create()->loadByStore();
        }

        public function getRegionJson() {
            return $this->_directoryHelper->getRegionJson();
        }
    public function getQuoteItems()
    {
        
       $quoteCart = $this->catalogSession->getQuoteItems() ?? [];
        $items = [];
        //echo "<pre>"; 
        //print_r($quoteCart); 
        //echo "</pre>";
        //die("Session Data Check"); // Page yahi ruk jayega aur data dikhayega
        foreach ($quoteCart as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                $items[] = [
                    'product' => $product,
                    'qty' => 1
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $items;
    }
}
