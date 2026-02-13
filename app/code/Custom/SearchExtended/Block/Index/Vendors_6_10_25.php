<?php

namespace Custom\SearchExtended\Block\Index;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Vnecoms\VendorsPage\Helper\Data as VendorsPageHelper;
use Business\VendorsVerification\Helper\Data as VendorsVerificationHelper;

class Vendors extends Template
{
    /**
     * @var VendorCollectionFactory
     */
    private $vendorCollectionFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var VendorsPageHelper
     */
    private $vendorsPageHelper;

    /**
     * @var VendorsVerificationHelper
     */
    private $vendorsVerificationHelper;

    public function __construct(
        Context $context,
        VendorCollectionFactory $vendorCollectionFactory,
        RequestInterface $request,
        VendorsPageHelper $vendorsPageHelper,
        VendorsVerificationHelper $vendorsVerificationHelper,
        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->request = $request;
        $this->vendorsPageHelper = $vendorsPageHelper;
        $this->vendorsVerificationHelper = $vendorsVerificationHelper;
        
        parent::__construct($context, $data);
    }

    /**
     * Retrieve a filtered vendor collection based on the search query.
     * This method is called from your vendors.phtml template.
     * @return \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection
     */
    public function getSearchCollection()
    {
        $collection = $this->vendorCollectionFactory->create();
        $query = $this->request->getParam('q');

        // This ensures the collection has all the attributes needed for the search results page
        $collection->addAttributeToSelect(['vendor_id', 'c_name', 'upload_logo', 'business_descriptions', 'company', 'b_name','country_id', 'business_type','website']);

        if ($query) {
            $collection->addAttributeToFilter([
                ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $query . '%']
            ]);
        }
         // --- Filter by search query ---
        if ($query) {
            $collection->addAttributeToFilter([
                ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%']
            ]);
        }
      
        // --- Filter by selected values ---
        $country = $this->request->getParam('svendor_country_id');
        $verified = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        if ($country) {
            $collection->addAttributeToFilter('country_id', $country);
        }

        if ($verified !== null && $verified !== '') {
            // filter using helper
            $filteredIds = [];
            foreach ($collection as $vendor) {
                if ($this->isVerifiedVendor($vendor->getId()) == ($verified == 1)) {
                    $filteredIds[] = $vendor->getId();
                }
            }
            $collection->addFieldToFilter('entity_id', ['in' => $filteredIds]);
        }

        if ($businessType) {
            $collection->addAttributeToFilter('business_type', $businessType);
        }

        return $collection;
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

        if ($pager) {
            $pager->setCollection($this->getSearchCollection());
            return $pager->toHtml();
        }

        return '';
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
public function getFilterData()
{
    // $collection = $this->getSearchCollection();

    // $vendorCountries = [];
    // $vendorVerifieds = [];
    // $businessTypes = [];

    // foreach ($collection as $vendor) {
    //     // Country ID
    //     if ($vendor->getCountryId()) {
    //         $vendorCountries[$vendor->getCountryId()] = $vendor->getCountryId();
    //     }

    //     // Verified
    //     $isVerified = $this->isVerifiedVendor($vendor->getId());
    //     $vendorVerifieds[$isVerified ? 1 : 0] = $isVerified ? 'Verified' : 'Unverified';

    //     // Business Type (just value, no translation)
    //     if ($vendor->getBusinessType()) {
    //         $businessTypes[$vendor->getBusinessType()] = $vendor->getBusinessType();
    //     }
    // }

    // return [
    //     'countries' => $vendorCountries,
    //     'verified' => $vendorVerifieds,
    //     'business_types' => $businessTypes
    // ];
    $collection = $this->vendorCollectionFactory->create();
        $collection->addAttributeToSelect(['country_id', 'business_type']);

        $vendorCountries = [];
        $vendorVerifieds = [];
        $businessTypes = [];

        foreach ($collection as $vendor) {
            $vendorCountries[$vendor->getCountryId()] = $vendor->getCountryId();
            $isVerified = $this->isVerifiedVendor($vendor->getId());
            $vendorVerifieds[$isVerified ? 1 : 0] = $isVerified ? 'Verified' : 'Unverified';
            if ($vendor->getBusinessType()) {
                $businessTypes[$vendor->getBusinessType()] = $vendor->getBusinessType();
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

}
