<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory; // नई निर्भरता (New dependency)

class InstantProvider extends AbstractInstantProvider implements InstantProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var VendorCollectionFactory
     */
    private $vendorCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory,
        ProductCollectionFactory $productCollectionFactory 
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Retrieves the search results by filtering vendors based on their attributes and product matches.
     *
     * @param int $storeId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];
        $pageSize = $limit;

        $query = trim((string)$this->request->getParam('q'));

        $collection = $this->vendorCollectionFactory->create();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->addAttributeToSelect(['vendor_id','b_name', 'c_name', 'business_descriptions','upload_logo']);

        if ($query) {
            //search the products and vendor ids
            $productVendorIds = $this->getVendorIdsFromProductSearch($query);
            
          
            $vendorAttributeFilter = [
               
                ['attribute' => 'b_name', 'like' => "%$query%"],
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
            ];
            
            // if we get vendor id it will add to collection
            if (!empty($productVendorIds)) {
               
                $vendorAttributeFilter[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }
            
           
            $collection->addAttributeToFilter($vendorAttributeFilter);
        }

        foreach ($collection as $vendor) {
            $logoUrl = '';
            $logo = $vendor->getData('upload_logo');
            // Correctly get the base media URL
            $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            if ($logo) {
                $logoUrl = $baseMediaUrl . $logo; // Correctly construct the URL
            }
            // $items[] = [
            //     'id'          => $vendor->getId(),
            //     'title'       => $vendor->getData('b_name'),   // use "title" directly
            //     'url'         => $this->getBaseUrl() . 'shop/v' . $vendor->getId(),
            //     'detail'      => $vendor->getData('business_descriptions'),
            //     'logo'        => $logoUrl, // if vendor has logo attribute
            // ];

            $storeCode = $this->storeManager->getStore()->getCode();

$items[] = [
    'id'     => $vendor->getId(),
    'title'  => $vendor->getData('b_name'),
    'url'    => $this->getBaseUrl() . $storeCode . '/shop/' . $vendor->getData('vendor_id'),
    'detail' => $vendor->getData('business_descriptions'),
    'logo'   => $logoUrl,
];
            //     $items[] = [
            //    'id'          => $vendor->getId(),
            //      'title'       => $vendor->getData('b_name'),   // use "title" directly
            //      'url'         => $this->getBaseUrl() . 'shop/' .  $vendor->getData('vendor_id'),
            //      'detail'      => $vendor->getData('business_descriptions'),
            //      'logo'        => $logoUrl, // if vendor has logo attribute
            //  ];
    
        }


        return $items;
    }

    /**
     * Gets the total size of the filtered collection.
     *
     * @param int $storeId
     * @return int
     */
    public function getSize(int $storeId): int
    {
        $collection = $this->vendorCollectionFactory->create();
        $query = trim((string)$this->request->getParam('q'));
        
        if ($query) {
            //search the products and related vendor ids 
            $productVendorIds = $this->getVendorIdsFromProductSearch($query);

          
            $vendorAttributeFilter = [
                
                ['attribute' => 'b_name', 'like' => "%$query%"],
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
            ];
            
            // 3. यदि उत्पादों से वेंडर ID मिली हैं, तो उन्हें OR कंडीशन में जोड़ दें
            if (!empty($productVendorIds)) {
                $vendorAttributeFilter[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }

            
            $collection->addAttributeToFilter($vendorAttributeFilter);
        }
        return $collection->getSize();
    }

    /**
     
     *
     * @param string $query
     * @return array
     */
    private function getVendorIdsFromProductSearch(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        
        
        $productCollection->addAttributeToSelect('vendor_id'); 
        
        
        $productCollection->addAttributeToFilter(
            [
                
                ['attribute' => 'name', 'like' => "%$query%"],
                ['attribute' => 'short_description', 'like' => "%$query%"],
            ]
        );
        
        
        $productCollection->addAttributeToFilter('vendor_id', ['notnull' => true]);

        
        $vendorIds = $productCollection->getColumnValues('vendor_id');
        return array_unique(array_filter($vendorIds));
    }


    public function map(array $documentData, int $storeId): array
    {
        return [
            'id'     => $documentData['id'] ?? null,
            'title'  => $documentData['name'] ?? '',    // Vendor name
            'url'    => $documentData['url'] ?? '',    // Vendor detail URL
            'detail' => $documentData['description'] ?? '', // Description
            'logo'   => $documentData['logo'] ?? '',   // Optional logo
        ];
    }
    
    public function getTemplate(): string
    {
        return 'Custom_SearchExtended::autocomplete/vendors.phtml';
    }

    private function getBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    
    public function getIdentifier(): string
    {
        return 'vnecoms_vendors';
    }

    /**
     * Updated to show the dynamic count of vendors matching the query.
     *
     * @return string
     */
    public function getTitle(): string
    {
        $totalCount = $this->getSize($this->storeManager->getStore()->getId());
        return __('Vendors (%1)', $totalCount); 
    }
}
