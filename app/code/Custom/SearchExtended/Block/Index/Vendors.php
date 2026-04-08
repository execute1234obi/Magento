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

    private $countryFactory;
    private $eavConfig;

    public function __construct(
        Context $context,
        VendorCollectionFactory $vendorCollectionFactory,
        RequestInterface $request,
        VendorsPageHelper $vendorsPageHelper,
        VendorsVerificationHelper $vendorsVerificationHelper,
        CountryFactory $countryFactory,
        EavConfig $eavConfig,
        array $data = []
    ) {
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->request = $request;
        $this->vendorsPageHelper = $vendorsPageHelper;
        $this->vendorsVerificationHelper = $vendorsVerificationHelper;
        $this->countryFactory = $countryFactory;
        $this->eavConfig = $eavConfig;

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
    $query = trim($this->request->getParam('q'));

    $collection->addAttributeToSelect([
        'vendor_id', 'c_name', 'upload_logo', 'business_descriptions',
        'company', 'b_name', 'country_id', 'business_type', 'website'
    ]);

    if ($query) {
        // --- Step 1: Vendor attributes match ---
        $collection->addAttributeToFilter([
            ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
            ['attribute' => 'b_name', 'like' => '%' . $query . '%'],
            ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%']
        ]);
    }

    // --- Step 2: Also find vendors who have products matching the query ---
    try {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollectionFactory = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $productCollection = $productCollectionFactory->create();

        $productCollection->addAttributeToSelect(['vendor_id', 'name', 'description', 'sku']);
        $productCollection->addAttributeToFilter([
            ['attribute' => 'name', 'like' => '%' . $query . '%'],
            ['attribute' => 'description', 'like' => '%' . $query . '%'],
            ['attribute' => 'sku', 'like' => '%' . $query . '%']
        ]);

        // Step 3: Extract unique vendor IDs from products
        $vendorIds = [];
        foreach ($productCollection as $product) {
            if ($product->getVendorId()) {
                $vendorIds[] = $product->getVendorId();
            }
        }
        $vendorIds = array_unique($vendorIds);

        // Step 4: Merge vendor filter — show vendors matching either condition
        if (!empty($vendorIds)) {
            $collection->getSelect()->orWhere('e.entity_id IN (?)', $vendorIds);
        }

    } catch (\Exception $e) {
        $this->_logger->error('Vendor product search failed: ' . $e->getMessage());
    }

    // --- Step 5: Apply extra filters (country, verified, business type) ---
    $country = $this->request->getParam('svendor_country_id');
    $verified = $this->request->getParam('svendor_is_verified');
    $businessType = $this->request->getParam('svendor_business_type');

    if ($country) {
        $collection->addAttributeToFilter('country_id', $country);
    }

    if ($verified !== null && $verified !== '') {
        $filteredIds = [];
        foreach ($collection as $vendor) {
            if ($this->isVerifiedVendor($vendor->getId()) == ($verified == 1)) {
                $filteredIds[] = $vendor->getId();
            }
        }
        if (!empty($filteredIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $filteredIds]);
        }
    }

    if ($businessType) {
        $collection->addAttributeToFilter('business_type', $businessType);
    }

    // Debug query if needed
     //echo $collection->getSelect(); exit;

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
           // $vendorCountries[$vendor->getCountryId()] = $vendor->getCountryId();
           if ($vendor->getCountryId()) {
    $countryCode = $vendor->getCountryId();

    if (!isset($vendorCountries[$countryCode])) {
        try {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $countryName = $country->getName();
        } catch (\Exception $e) {
            $countryName = $countryCode;
        }

        $vendorCountries[$countryCode] = $countryName;
    }
}
            $isVerified = $this->isVerifiedVendor($vendor->getId());
            $vendorVerifieds[$isVerified ? 1 : 0] = $isVerified ? 'Verified' : 'Unverified';
            // if ($vendor->getBusinessType()) {
            //     $businessTypes[$vendor->getBusinessType()] = $vendor->getBusinessType();
            // }
         if ($vendor->getBusinessType()) {
    $value = $vendor->getBusinessType();

    if (!isset($businessTypes[$value])) {

        $attribute = $this->eavConfig->getAttribute('vendor', 'business_type');

        $label = $attribute && $attribute->usesSource()
            ? $attribute->getSource()->getOptionText($value)
            : $value;

        $businessTypes[$value] = $label;
    }
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
