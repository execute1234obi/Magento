<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Mirasvit\Search\Index\AbstractInstantProvider;
use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;

class InstantProvider extends AbstractInstantProvider
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

    public function __construct(
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];
        $pageSize = $limit;

        $query = trim((string)$this->request->getParam('q'));

        $collection = $this->vendorCollectionFactory->create();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->addAttributeToSelect(['b_name', 'c_name', 'business_descriptions','upload_logo']);

        if ($query) {
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'b_name', 'like' => "%$query%"],
                    ['attribute' => 'c_name', 'like' => "%$query%"],
                    ['attribute' => 'business_descriptions', 'like' => "%$query%"],
                ]
            );
        }

        foreach ($collection as $vendor) {
             $logoUrl = '';
        $logo = $vendor->getData('upload_logo');
        // Correctly get the base media URL
        $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if ($logo) {
            $logoUrl = $baseMediaUrl . $logo; // Correctly construct the URL
        }
    $items[] = [
        'id'          => $vendor->getId(),
        'title'       => $vendor->getData('b_name'),   // use "title" directly
        'url'         => $this->getBaseUrl() . 'shop/v' . $vendor->getId(),
        'detail'      => $vendor->getData('business_descriptions'),
        'logo'        => $logoUrl, // if vendor has logo attribute
    ];
}


        return $items;
    }

    public function getSize(int $storeId): int
    {
        // $collection = $this->vendorCollectionFactory->create();
        // return $collection->getSize();
         $collection = $this->vendorCollectionFactory->create();
    // Apply the same filters/search query here as in getItems() so count matches search
    $query = trim((string)$this->request->getParam('q'));
    if ($query) {
        $collection->addAttributeToFilter(
            [
                ['attribute' => 'b_name', 'like' => "%$query%"],
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
            ]
        );
    }
    return $collection->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        return [
        'id'     => $documentData['id'] ?? null,
        'title'  => $documentData['name'] ?? '',   // Vendor name
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

    public function getTitle(): string
    {
        $collection = $this->vendorCollectionFactory->create();
        $totalCount = $collection->getSize();
        return __('🔥 Vendors Title Reached'); 
        //return __('Vendors (%1)', $totalCount);
    }
}
