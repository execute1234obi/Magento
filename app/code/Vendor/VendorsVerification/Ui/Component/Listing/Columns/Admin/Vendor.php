<?php
namespace Vendor\BookAdvertisement\Ui\Component\Listing\Columns\Admin;

use Exception;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;


/**
 * Class Content
 * @package Vendor\Advertisement\Ui\Component\Listing\Column\Admin
 */
class Vendor extends Column
{
    /**
     * @var FilterProvider
     */
    public $filterProvider;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;
    
    /**
     * @var \Vnecoms\Vendors\Helper\Data
     */
    protected $_vendorHelper;

    
    
    protected $storeRepository;


    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterProvider $filterProvider,
        UrlInterface $urlBuilder,    
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,       
        array $components = [],
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->urlBuilder = $urlBuilder;
        $this->_vendorFactory = $vendorFactory;
        $this->_vendorHelper = $vendorHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     * @throws Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
			
            
            $fieldName = $this->getData('name');
            
            
            foreach ($dataSource['data']['items'] as & $item) {
				$vendor = $this->_vendorFactory->create()->load($item['vendor_id']);
				
                //$advert = new DataObject($item);                
                /*if ($item['type'] === Type::IMAGE && $item['image']) {
                    $item[$fieldName . '_src'] = $path . $item['image'];
                } else {
                    $item[$fieldName] = $this->filterProvider->getPageFilter()->filter($item[$fieldName]);
                }*/                
                $venddorInfo  = $this->_vendorHelper->getVendorStoreName($vendor->getId());
				$venddorInfo .= "| " .$vendor->getName();
				$venddorInfo .= "| ID : ". $vendor->getVendorId();
				$item[$fieldName] = $venddorInfo;
            }
        }

        return $dataSource;
    }
    
    
}
