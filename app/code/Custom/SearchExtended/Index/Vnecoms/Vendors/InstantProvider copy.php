<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Mirasvit\Search\Index\AbstractInstantProvider;
use Vnecoms\Vendor\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;

/**
 * InstantProvider class for Mirasvit search autocomplete.
 * This class is responsible for providing data to the autocomplete dropdown.
 */
class InstantProvider extends AbstractInstantProvider
{
    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * InstantProvider constructor.
     * @param VendorCollectionFactory $vendorCollectionFactory
     * @param array $data
     */
    public function __construct(
        VendorCollectionFactory $vendorCollectionFactory,
        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        parent::__construct($data);
    }

    /**
     * Get a collection of vendors based on the search query.
     *
     * @return \Vnecoms\Vendor\Model\ResourceModel\Vendor\Collection
     */
    protected function getVendorCollection()
    {
        // Get the search query from the abstract provider's query object
        $queryText = $this->getQuery()->getQueryText();
        $collection = $this->vendorCollectionFactory->create();

        // Select the attributes relevant to the search and display
        $collection->addAttributeToSelect([
            'vendor_id', 
            'c_name', 
            'upload_logo', 
            'business_descriptions', 
            'company', 
            'b_name'
        ]);

        // Apply the search filter if a query exists
        if ($queryText) {
            $collection->addFieldToFilter([
                ['attribute' => 'c_name', 'like' => '%' . $queryText . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $queryText . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $queryText . '%']
            ]);
        }

        return $collection;
    }

    /**
     * Get the search results for the autocomplete dropdown.
     *
     * @param int $storeId
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getItems(int $storeId, int $limit, int $page = 1): array
    {
        $items = [];
        $collection = $this->getVendorCollection();

        $collection->setPageSize($limit);
        $collection->setCurPage($page);
        $collection->load();

        foreach ($collection as $vendor) {
            $items[] = [
                'id'    => $vendor->getId(),
                'name'  => $vendor->getName(),
                'url'   => '/vendor/index/view/id/' . $vendor->getId(),
                // Add any other data you need for the autocomplete display
            ];
        }

        return $items;
    }

    /**
     * Get the total number of search results.
     *
     * @param int $storeId
     * @return int
     */
    public function getSize(int $storeId): int
    {
        $collection = $this->getVendorCollection();
        return $collection->getSize();
    }
}
