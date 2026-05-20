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
     */
    public function beforeLoad(Collection $collection, $printQuery = false, $logQuery = false)
    {
        $this->applyVendorFilter($collection);
        return [$printQuery, $logQuery];
    }

    /**
     * Overwrite getSize safely using Magento's core clone functionality
     */
    public function aroundGetSize(Collection $collection, \Closure $proceed)
    {
        $this->applyVendorFilter($collection);
        
        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        if (!$country && !$verified && !$businessType) {
            return $proceed();
        }

        // Safe Magento Way: Clone select structure to get the precise count
        $countSelect = clone $collection->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        
        $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'));
        
        $count = (int)$collection->getConnection()->fetchOne($countSelect);

        // Force Mirasvit/Magento tab components to notice that products are present
        if ($count > 0 && method_exists($collection, 'setSearchSearchResult')) {
            $collection->setPageSize($collection->getPageSize());
        }

        return $count;
    }

    /**
     * Core filtration logic
     */
    private function applyVendorFilter(Collection $collection)
    {
        if ($collection->getFlag('custom_vendor_filter_applied')) {
            return;
        }

        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        if (!$country && !$verified && !$businessType) {
            return;
        }

        $collection->setFlag('custom_vendor_filter_applied', true);

        // Fetch Matching Vendor IDs
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
        if (empty($vendorIds)) {
            $vendorIds = [0];
        }

        // Clean out any rogue multi-vendor overlapping clauses safely
        $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
        foreach ($where as $key => $condition) {
            if (strpos($condition, 'vendor_id') !== false) {
                unset($where[$key]);
            }
        }
        $collection->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);

        // Apply vendor filter constraint
        $collection->getSelect()->where('e.vendor_id IN (?)', $vendorIds);

        // EXTRA FIX: Force layout state block override
        if ($collection->getSelect()->getPart(\Zend_Db_Select::LIMIT_COUNT) == null) {
            $collection->getSelect()->limit(20); // Default items limit ensure fallback
        }
    }
}