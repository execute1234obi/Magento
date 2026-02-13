<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Mirasvit\Search\Api\Data\Index\InstantProviderInterface;
use Mirasvit\Search\Index\AbstractInstantProvider;

class InstantProvider extends AbstractInstantProvider
{
    /**
     * {@inheritdoc}
     */
    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];
        $pageSize = $limit;
        
        // This is a temporary way to get the collection.
        // It's better to use dependency injection.
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get(\Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory::class)->create();
        
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        $collection->addAttributeToSelect(['b_name', 'c_name', 'business_descriptions']);
        $collection->load();

        foreach ($collection as $vendor) {
            $items[] = [
                'id'    => $vendor->getId(),
                'name'  => $vendor->getName(),
                'url'   => '/vendor/index/view/id/' . $vendor->getId(),
            ];
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(int $storeId): int
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get(\Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory::class)->create();
        $collection->load();
        
        return $collection->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documentData, int $storeId): array
    {
        $mappedData = [
            'vendor_id'             => $documentData['id'] ?? null,
            'b_name'                => $documentData['name'] ?? '',
            'vendor_url'            => $documentData['url'] ?? '',
            'business_descriptions' => $documentData['business_descriptions'] ?? '',
            'c_name'                => $documentData['c_name'] ?? '',
        ];
        
        return $mappedData;
    }
}
