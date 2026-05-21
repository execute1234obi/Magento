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

    /**
     * Trigger hamesha hoga load se pehle
     */
    public function beforeLoad(Collection $collection, $printQuery = false, $logQuery = false)
    {
        $this->applyVendorFilter($collection);
        return [$printQuery, $logQuery];
    }

    /**
     * Loop-Free Count Overwrite
     */
    // public function aroundGetSize(Collection $collection, \Closure $proceed)
    // {
    //     $country = $this->request->getParam('svendor_country_id');
    //     if (!$country) {
    //         return $proceed(); // Agar filter nahi hai toh normal chalne do
    //     }

    //     $this->applyVendorFilter($collection);

    //     // ATTENTION: Hum $proceed() call hi nahi kar rahe hain taaki core loop na bane!
    //     $countSelect = clone $collection->getSelect();
    //     $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
    //     $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
    //     $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
    //     $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
    //     $countSelect->columns(new \Zend_Db_Expr('COUNT(DISTINCT e.entity_id)'));

    //     $count = (int)$collection->getConnection()->fetchOne($countSelect);

    //     // Directly sync collection property inside
    //     $reflection = new \ReflectionClass($collection);
    //     if ($reflection->hasProperty('_totalRecords')) {
    //         $property = $reflection->getProperty('_totalRecords');
    //         $property->setAccessible(true);
    //         $property->setValue($collection, $count);
    //     }

    //     return $count;
    // }
    public function aroundGetSize(Collection $collection, \Closure $proceed)
{
    $this->applyVendorFilter($collection);

    return $proceed();
}

    /**
     * Core Database Logic (Jo phpMyAdmin query se 100% verified hai)
     */
    // private function applyVendorFilter(Collection $collection)
    // {
    //     if ($collection->getFlag('custom_vendor_filter_applied')) {
    //         return;
    //     }

    //     $country = $this->request->getParam('svendor_country_id');
    //     if (!$country) {
    //         return;
    //     }

    //     $collection->setFlag('custom_vendor_filter_applied', true);

    //     // Fetch Vendor IDs
    //     $vendorCollection = $this->vendorCollectionFactory->create();
    //     $vendorCollection->addFieldToFilter('country_id', $country);
    //     $vendorIds = $vendorCollection->getAllIds();
        
    //     if (empty($vendorIds)) {
    //         $vendorIds = [0];
    //     }

    //     // Sabse zaroori: Mirasvit aur Vnecoms ke purane restrictive filters ko clear karna
    //     $where = $collection->getSelect()->getPart(\Zend_Db_Select::WHERE);
    //     foreach ($where as $key => $condition) {
    //         if (strpos($condition, 'vendor_id') !== false || strpos($condition, 'entity_id') !== false) {
    //             unset($where[$key]);
    //         }
    //     }
    //     $collection->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);

    //     // Direct SQL connection bridge
    //     $collection->getSelect()->where('e.vendor_id IN (?)', $vendorIds);
    //     $collection->getSelect()->group('e.entity_id');
    // }
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

    $vendorCollection = $this->vendorCollectionFactory->create();

    if ($country) {
        $vendorCollection->addFieldToFilter('country_id', $country);
    }

    if ($verified) {
        $vendorCollection->addFieldToFilter('status', $verified);
    }

    /*
    if ($businessType) {
        $vendorCollection->addFieldToFilter('business_type', $businessType);
    }
    */

    /*
     IMPORTANT:
     product.vendor_id matches vendor ENTITY_ID
     NOT vendor_id column (v1/v2/v3)
    */
    $vendorEntityIds = $vendorCollection->getColumnValues('entity_id');

    if (empty($vendorEntityIds)) {
        $vendorEntityIds = [0];
    }

    $select = $collection->getSelect();

    // duplicate condition cleanup
    $where = $select->getPart(\Zend_Db_Select::WHERE);

    foreach ($where as $key => $condition) {
        if (strpos($condition, 'vendor_id') !== false) {
            unset($where[$key]);
        }
    }

    $select->setPart(\Zend_Db_Select::WHERE, $where);

    // FINAL FILTER
    $select->where('e.vendor_id IN (?)', $vendorEntityIds);

    // avoid duplicate rows
    $select->group('e.entity_id');
}
}