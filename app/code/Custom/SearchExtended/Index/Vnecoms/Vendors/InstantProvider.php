<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Custom\SearchExtended\Model\VendorFilter;

class InstantProvider extends AbstractInstantProvider implements InstantProviderInterface
{
    private $storeManager;
    private $request;
    private $vendorCollectionFactory;
    private $vendorFilter;

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory,
        VendorFilter $vendorFilter 
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->vendorFilter = $vendorFilter;        
    }

    /**
     * Retrieves the search results by filtering vendors based on their attributes and product matches.
     */
    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];
        $query = trim((string)$this->request->getParam('q'));

        $collection = $this->vendorCollectionFactory->create();

        // 1. Select attributes needed for display
        $collection->addAttributeToSelect([
            'vendor_id',
            'b_name',
            'c_name',
            'business_descriptions',
            'upload_logo'
        ]);

        // 2. Apply Centralized Filter (Status + Membership Expiry)
        $this->vendorFilter->apply($collection);

        $collection->setPageSize($limit);
        $collection->setCurPage($page);

        if ($query) {
            // 3. Get Vendor IDs from Product Search (DRY logic from VendorFilter)
            $productVendorIds = $this->vendorFilter->getVendorIdsByProductQuery($query);

            $vendorAttributeFilter = [
                ['attribute' => 'b_name', 'like' => "%$query%"],
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
            ];

            if (!empty($productVendorIds)) {
                $vendorAttributeFilter[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }

            $collection->addAttributeToFilter($vendorAttributeFilter);
        }

        foreach ($collection as $vendor) {
            $logoUrl = '';
            $logo = trim((string) $vendor->getData('upload_logo'));
            $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            if ($logo !== '') {
                $logoUrl = $baseMediaUrl . ltrim($logo, '/');
            }

            $storeCode = $this->storeManager->getStore()->getCode();

            $items[] = [
                'id'     => $vendor->getId(),
                'title'  => $vendor->getData('b_name') ?: $vendor->getData('c_name'),
                'url'    => $this->getBaseUrl() . $storeCode . '/shop/' . $vendor->getData('vendor_id'),
                'detail' => $vendor->getData('business_descriptions'),
                'logo'   => $logoUrl,
            ];
        }

        return $items;
    }

    /**
     * Gets the total size of the filtered collection.
     */
    public function getSize(int $storeId): int
    {
        $collection = $this->vendorCollectionFactory->create();
        $this->vendorFilter->apply($collection);

        $query = trim((string)$this->request->getParam('q'));

        if ($query) {
            $productVendorIds = $this->vendorFilter->getVendorIdsByProductQuery($query);

            $vendorAttributeFilter = [
                ['attribute' => 'b_name', 'like' => "%$query%"],
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
            ];

            if (!empty($productVendorIds)) {
                $vendorAttributeFilter[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }

            $collection->addAttributeToFilter($vendorAttributeFilter);
        }

        return $collection->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        return [
            'id'     => $documentData['id'] ?? null,
            'title'  => $documentData['name'] ?? '',
            'url'    => $documentData['url'] ?? '',
            'detail' => $documentData['description'] ?? '',
            'logo'   => $documentData['logo'] ?? '',
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

    public function getTitle(): string
    {
        $totalCount = $this->getSize($this->storeManager->getStore()->getId());
        return __('Vendors (%1)', $totalCount); 
    }
}
