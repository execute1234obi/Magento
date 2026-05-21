<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory;

class ProductCollectionPlugin
{
    protected $request;
    protected $vendorCollectionFactory;

    public function __construct(
        RequestInterface $request,
        CollectionFactory $vendorCollectionFactory
    ) {
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

public function beforeLoad($collection, $printQuery = false, $logQuery = false)
    {
       $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');
        // Skip if no filters selected
        if (!$country && !$verified && !$businessType) {
            return;
        }

        // Vendor collection
        $vendorCollection = $this->vendorCollectionFactory->create();
        
        // Apply vendor filters
        if ($country) {
    $vendorCollection->addFieldToFilter('country_id', $country);
}

if ($verified) {
    $vendorCollection->addFieldToFilter('status', $verified);
}

        // if ($businessType) {
        //     $vendorCollection->addAttributeToFilter('business_type', $businessType);
        // }
 //print_r($vendorCollection->getSelect()->__toString());
//exit;
        // Get matching vendor IDs
        $vendorIds = $vendorCollection->getAllIds();

        // No vendors found
        if (empty($vendorIds)) {
            $collection->addFieldToFilter('entity_id', 0);
            return;
        }

      
    $collection->getSelect()->where(
    'e.vendor_id IN (?)',
    $vendorIds
);
//echo "PLUGIN QUERY:<pre>";
//echo $collection->getSelect()->__toString();
//exit;
         return [$printQuery, $logQuery];
    }
}