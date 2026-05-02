<?php

namespace Custom\SearchExtended\Block\Index;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Vnecoms\VendorsPage\Helper\Data as VendorsPageHelper;
use Business\VendorsVerification\Helper\Data as VendorsVerificationHelper;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config as EavConfig;
use Custom\SearchExtended\Model\VendorFilter;

/* ADD THESE USES */
use Magento\Catalog\Model\Layer\Search;
use Magento\Catalog\Model\Layer\Search\FilterableAttributeList;
use Magento\Catalog\Model\Layer\FilterList;

class Vendors extends Template
{
    private $vendorCollectionFactory;
    private $request;
    private $vendorsPageHelper;
    private $vendorsVerificationHelper;
    private $countryFactory;
    private $eavConfig;
    private $vendorFilter;

    /* ADD THESE */
    private $searchLayer;
    private $filterableAttributes;
    private $filterList;

    public function __construct(
        Context $context,
        VendorCollectionFactory $vendorCollectionFactory,
        RequestInterface $request,
        VendorsPageHelper $vendorsPageHelper,
        VendorsVerificationHelper $vendorsVerificationHelper,
        CountryFactory $countryFactory,
        EavConfig $eavConfig,
        VendorFilter $vendorFilter,

        /* ADD THESE */
        Search $searchLayer,
        FilterableAttributeList $filterableAttributes,
        FilterList $filterList,

        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->request = $request;
        $this->vendorsPageHelper = $vendorsPageHelper;
        $this->vendorsVerificationHelper = $vendorsVerificationHelper;
        $this->countryFactory = $countryFactory;
        $this->eavConfig = $eavConfig;
        $this->vendorFilter = $vendorFilter;

        /* ADD THESE */
        $this->searchLayer = $searchLayer;
        $this->filterableAttributes = $filterableAttributes;
        $this->filterList = $filterList;

        parent::__construct($context, $data);
    }

    /* =======================================================
       PRODUCT FILTERS METHOD
       ======================================================= */
    public function getProductFilters()
    {
        return $this->filterList->getFilters($this->searchLayer);
    }

    /* =======================================================
       PRODUCT FILTER LINKS METHOD
       ======================================================= */
    public function getFilterItemUrl($item)
    {
        return $item->getUrl();
    }

    /* =======================================================
       CHECK SELECTED FILTER
       ======================================================= */
    public function isSelectedFilter($filter, $item)
    {
        $requestVar = $filter->getRequestVar();
        $current = $this->request->getParam($requestVar);

        if (!$current) {
            return false;
        }

        $values = explode(',', $current);

        return in_array($item->getValueString(), $values);
    }
    public function getSearchCollection()
    {
        $collection = $this->vendorCollectionFactory->create();
        
        // 1. Apply Centralized Rules (Status + Membership Expiry)
        $this->vendorFilter->apply($collection);

        $query = trim((string)$this->request->getParam('q'));

        $collection->addAttributeToSelect([
            'vendor_id', 'c_name', 'upload_logo', 'business_descriptions',
            'company', 'b_name', 'country_id', 'business_type', 'website'
        ]);

        /* ================= INTEGRATED SEARCH ================= */
        if ($query) {
            // Get Vendor IDs from Products (Synced with Autocomplete)
            $productVendorIds = $this->vendorFilter->getVendorIdsByProductQuery($query);

            // Create a single OR condition array
            $searchFilters = [
                ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%']
            ];

            // If products found, add their vendors to the OR condition
            if (!empty($productVendorIds)) {
                $searchFilters[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }

            $collection->addAttributeToFilter($searchFilters);
        }
        

        /* ================= ADDITIONAL FILTERS ================= */
        $this->applyExtraFilters($collection);
       // echo '<pre>';
       // print_r($collection->getSelect()->__toString());
       // echo '</pre>';
       // exit;
        return $collection;
    }

    /**
     * Helper to apply sidebar filters
     */
   private function applyExtraFilters($collection)
{
    $country = $this->request->getParam('svendor_country_id');
    $verified = $this->request->getParam('svendor_is_verified');
    $businessType = $this->request->getParam('svendor_business_type');

    if ($country) {
        $collection->addAttributeToFilter('country_id', $country);
    }

    if ($verified !== null && $verified !== '') {
        // Fix: getResource() ki jagah direct table name string use karein ya filter se lein
        $tableName = 'business_vendor_verification'; 

        $collection->getSelect()->joinLeft(
            ['vv' => $tableName],
            'e.entity_id = vv.vendor_id',
            []
        );
        
        if ($verified == 1) {
            $collection->getSelect()->where('vv.is_verified = 1');
        } else {
            $collection->getSelect()->where('(vv.is_verified = 0 OR vv.is_verified IS NULL)');
        }
    }

    if ($businessType) {
        $collection->addAttributeToFilter('business_type', $businessType);
    }
}
   /**
     * Get the item's URL from the search result.
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getItemUrl($item)
    {
        // This assumes the vendor model has a getUrl() method.
        // If not, we'll need to build the URL manually.
        return $item->getUrl();
    }
    
    /**
     * Returns the vendor's display name.
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getVendorName($item)
    {
        return $item->getBName() ?: $item->getCName();
    }
    
    /**
     * Returns the vendor's business description.
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getBusinessDescriptions($item)
    {
        return $item->getBusinessDescriptions();
    }
    
    /**
     * Return pager html for the current collection.
     * @return string
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('pager');

        if (!$pager) {
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class);
            if ($pager) {
                $pager->setTemplate('Magento_Theme::html/pager.phtml');
                $pager->setShowPerPage(true);
            }
        }

        if ($pager) {
            $pager->setCollection($this->getSearchCollection());
            return $pager->toHtml();
        }

        return '';
    }

    /**
     * Backward compatible alias used by the vendor template.
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getPager();
    }

    /**
     * Checks if a vendor is verified.
     * @param int $vendorId
     * @return bool
     */
    public function isVerifiedVendor($vendorId)
    {
        return $this->vendorsVerificationHelper->IsVerifiedVendor($vendorId);
    }
    /**
 * Get filter data based on current search collection
 * @return array
 */
// public function getFilterData()
// {
//     $collection = $this->vendorCollectionFactory->create();
//      $this->vendorFilter->apply($collection);
//    // $collection = $this->getSearchCollection();
//         $collection->addAttributeToSelect(['country_id', 'business_type']);

//         $vendorCountries = [];
//         $vendorVerifieds = [];
//         $businessTypes = [];

//         foreach ($collection as $vendor) {
//            // $vendorCountries[$vendor->getCountryId()] = $vendor->getCountryId();
//            if ($vendor->getCountryId()) {
//     $countryCode = $vendor->getCountryId();

//     if (!isset($vendorCountries[$countryCode])) {
//         try {
//             $country = $this->countryFactory->create()->loadByCode($countryCode);
//             $countryName = $country->getName();
//         } catch (\Exception $e) {
//             $countryName = $countryCode;
//         }

//         $vendorCountries[$countryCode] = $countryName;
//     }
// }
//             $isVerified = $this->isVerifiedVendor($vendor->getId());
//             $vendorVerifieds[$isVerified ? 1 : 0] = $isVerified ? 'Verified' : 'Unverified';
//             // if ($vendor->getBusinessType()) {
//             //     $businessTypes[$vendor->getBusinessType()] = $vendor->getBusinessType();
//             // }
//          if ($vendor->getBusinessType()) {
//     $value = $vendor->getBusinessType();

//     if (!isset($businessTypes[$value])) {

//         $attribute = $this->eavConfig->getAttribute('vendor', 'business_type');

//         $label = $attribute && $attribute->usesSource()
//             ? $attribute->getSource()->getOptionText($value)
//             : $value;

//         $businessTypes[$value] = $label;
//     }
// }
//         }
//     echo '<pre>';
// print_r([
//     'countries' => $vendorCountries,
//     'verified' => $vendorVerifieds,
//     'business_types' => $businessTypes
// ]);
// echo '</pre>';
// exit;
//         return [
//             'countries' => $vendorCountries,
//             'verified' => $vendorVerifieds,
//             'business_types' => $businessTypes
//         ];

// }

public function getFilterData()
{
    // 🔥 IMPORTANT: use SEARCH collection (not raw)
    $collection = $this->getSearchCollection();

    // Select attributes
    $collection->addAttributeToSelect([
        'country_id',
        'business_type'
    ]);

    $vendorCountries = [];
    $vendorVerifieds = [];
    $businessTypes   = [];

    foreach ($collection as $vendor) {

        // COUNTRY
        $countryCode = $vendor->getCountryId();

        if ($countryCode && !isset($vendorCountries[$countryCode])) {
            try {
                $country = $this->countryFactory->create()->loadByCode($countryCode);
                $countryName = $country->getName();
            } catch (\Exception $e) {
                $countryName = $countryCode;
            }

            $vendorCountries[$countryCode] = $countryName;
        }

        // VERIFIED
        $isVerified = $this->isVerifiedVendor($vendor->getId());

        $vendorVerifieds[$isVerified ? 1 : 0] =
            $isVerified ? 'Verified' : 'Unverified';

        // BUSINESS TYPE
        $value = $vendor->getBusinessType();

        if ($value && !isset($businessTypes[$value])) {

            $attribute = $this->eavConfig->getAttribute('vendor', 'business_type');

            $label = ($attribute && $attribute->usesSource())
                ? $attribute->getSource()->getOptionText($value)
                : $value;

            $businessTypes[$value] = $label;
        }
    }

    return [
        'countries' => $vendorCountries,
        'verified' => $vendorVerifieds,
        'business_types' => $businessTypes
    ];
}
/**
 * Get currently selected filter values from request
 * @return array
 */
public function getSelectedFilters()
{
    return [
        'country' => $this->request->getParam('svendor_country_id', ''),
        'verified' => $this->request->getParam('svendor_is_verified', ''),
        'business_type' => $this->request->getParam('svendor_business_type', ''),
    ];
}
public function getVendorResultCount()
{
    return $this->getSearchCollection()->getSize();
}
}