<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
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
     * Apply filter before collection load
     *
     * @param Collection $collection
     * @return void
     */
   public function beforeLoad(
    Collection $collection,
    $printQuery = false,
    $logQuery = false
) {
    $this->applyVendorFilter($collection);

    return [$printQuery, $logQuery];
}

    /**
     * Apply filter before size calculation
     * (Fix pagination/count issue)
     *
     * @param Collection $collection
     * @return void
     */
    public function beforeGetSize(Collection $collection)
    {
        $this->applyVendorFilter($collection);
    }

    /**
     * Common vendor filter logic
     *
     * @param Collection $collection
     * @return void
     */
    private function applyVendorFilter(Collection $collection)
    {
        // Prevent duplicate filter apply
        if ($collection->getFlag('custom_vendor_filter_applied')) {
            return;
        }

        $collection->setFlag('custom_vendor_filter_applied', true);

        // Request params
        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        // Skip if no filter selected
        if (!$country && !$verified && !$businessType) {
            return;
        }

        // Vendor collection
        $vendorCollection = $this->vendorCollectionFactory->create();

        /**
         * Country filter
         */
        if ($country) {
            $vendorCollection->addFieldToFilter(
                'country_id',
                $country
            );
        }

        /**
         * Verified filter
         */
        if ($verified) {
            $vendorCollection->addFieldToFilter(
                'status',
                $verified
            );
        }

        /**
         * Business type filter
         */
        if ($businessType) {
            $vendorCollection->addAttributeToFilter(
                'business_type',
                $businessType
            );
        }

        // Get matching vendor IDs
        $vendorIds = $vendorCollection->getAllIds();

        // No vendors found
        if (empty($vendorIds)) {
            $collection->addFieldToFilter('entity_id', 0);
            return;
        }

        /**
         * Remove existing vendor filters
         * added by Vnecom/Mirasvit
         */
        $where = $collection->getSelect()->getPart(
            \Zend_Db_Select::WHERE
        );

        foreach ($where as $key => $condition) {

            if (strpos($condition, 'e.vendor_id') !== false) {
                unset($where[$key]);
            }
        }

        $collection->getSelect()->setPart(
            \Zend_Db_Select::WHERE,
            $where
        );

        /**
         * Apply custom vendor filter
         */
        $collection->getSelect()->where(
            'e.vendor_id IN (?)',
            $vendorIds
        );

        /**
         * Debug query
         */
        /*
        echo "<pre>";
        echo $collection->getSelect()->__toString();
        exit;
        */
    }
}