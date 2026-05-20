<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Elasticsearch\SearchAdapter\Mapper;

class ElasticsearchMapperPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var VendorCollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @param RequestInterface $request
     * @param VendorCollectionFactory $vendorCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory
    ) {
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * Inject Vendor Filters into Elasticsearch query array
     *
     * @param Mapper $subject
     * @param array $result
     * @return array
     */
    public function afterBuildQuery(Mapper $subject, array $result)
    {
        // Get URL parameters
        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        // If no vendor filters are applied in the URL, return unchanged query
        if (!$country && !$verified && !$businessType) {
            return $result;
        }

        // Fetch matching Vendor IDs
        $vendorCollection = $this->vendorCollectionFactory->create();

        if ($country) {
            $vendorCollection->addFieldToFilter('country_id', $country);
        }
        if ($verified) {
            $vendorCollection->addFieldToFilter('status', $verified);
        }
        if ($businessType) {
            $vendorCollection->addAttributeToFilter('business_type', $businessType);
        }

        $vendorIds = $vendorCollection->getAllIds();

        // If filters are active but no vendors match, force Elasticsearch to yield empty results
        if (empty($vendorIds)) {
            $vendorIds = [0]; 
        }

        /**
         * Append the vendor_id filter to Elasticsearch query body.
         * Note: Ensure 'vendor_id' is configured as "Use in Search Results" 
         * or "Use in Layered Navigation" in Product Attributes so it is indexed.
         */
        $result['body']['query']['bool']['filter'][] = [
            'terms' => [
                'vendor_id' => $vendorIds
            ]
        ];

        return $result;
    }
}