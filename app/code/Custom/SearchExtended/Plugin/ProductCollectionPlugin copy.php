<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory;

class ProductCollectionPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param CollectionFactory $vendorCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        CollectionFactory $vendorCollectionFactory
    ) {
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * Apply vendor filters on product collection
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @return void
     */
    public function beforeLoad($collection)
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
       
    }

// public function beforeLoad($collection)
// {
//     $country      = $this->request->getParam('svendor_country_id');
//     $verified     = $this->request->getParam('svendor_is_verified');
//     $businessType = $this->request->getParam('svendor_business_type');

//     // Skip if no filters selected
//     if (!$country && !$verified && !$businessType) {
//         return;
//     }

//     // Vendor collection
//     $vendorCollection = $this->vendorCollectionFactory->create();

//     // Country filter
//     if ($country) {
//         $vendorCollection->addFieldToFilter(
//             'country_id',
//             $country
//         );
//     }

//     // Verified filter
//     if ($verified) {
//         $vendorCollection->addFieldToFilter(
//             'status',
//             $verified
//         );
//     }

//     // Business type filter
//     if ($businessType) {
//         $vendorCollection->addAttributeToFilter(
//             'business_type',
//             $businessType
//         );
//     }

//     // Get matching vendor IDs
//     $vendorIds = $vendorCollection->getAllIds();

//     // No vendors found
//     if (empty($vendorIds)) {
//         $collection->addFieldToFilter('entity_id', 0);
//         return;
//     }

//     /*
//      * Remove existing vendor filters
//      * added by Vnecom/Mirasvit
//      */
//     $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);

//     foreach ($where as $key => $condition) {

//         if (strpos($condition, 'vendor_id') !== false) {
//             unset($where[$key]);
//         }
//     }

//     $collection->getSelect()->setPart(
//         \Zend_Db_Select::WHERE,
//         $where
//     );

//     // Apply selected vendor filter
//     $collection->getSelect()->where(
//         'e.vendor_id IN (?)',
//         $vendorIds
//     );

//     // Debug query
    
//     // echo "PLUGIN QUERY:<pre>";
//     // echo $collection->getSelect()->__toString();
//     // exit;
    
// }
}