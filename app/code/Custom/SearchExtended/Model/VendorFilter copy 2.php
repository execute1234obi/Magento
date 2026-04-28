<?php

namespace Custom\SearchExtended\Model;

use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Model\Vendor;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class VendorFilter
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param ResourceConnection $resource
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Apply common filters (Status + Membership Expiry) to vendor collection
     * central logic for both Autocomplete and Search Page.
     * * @param \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection $collection
     * @return \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection
     */
    public function apply($collection)
    {
        $select = $collection->getSelect();
        $fromParts = $select->getPart(\Zend_Db_Select::FROM);
        $membershipTable = $this->resource->getTableName('ves_vendor_membership_transaction');

        // 1. Basic Filter: Approved Vendors only
        $collection->addAttributeToFilter('status', Vendor::STATUS_APPROVED);

        // 2. Membership Join & Expiry Logic
        if (!isset($fromParts['vmt'])) {
            $select->joinLeft(
                ['vmt' => $membershipTable],
                'e.entity_id = vmt.vendor_id',
                []
            );

            // Latest Transaction Only
            // $select->where("vmt.transaction_id IN (
            //     SELECT MAX(t.transaction_id) 
            //     FROM {$membershipTable} t 
            //     GROUP BY t.vendor_id
            // ) OR vmt.transaction_id IS NULL");

            // Expiry Check: Membership active honi chahiye ya fir vendor bina membership ke allow hona chahiye
            // $select->where(
            //     "(vmt.transaction_id IS NULL OR DATE_ADD(vmt.created_at, INTERVAL vmt.duration MONTH) >= NOW())"
            // );
        }

        // Avoid duplicate vendor rows due to joins
        $select->group('e.entity_id');
         //print_r( $select ->__toString() ); die; 
        return $collection;
    }

    /**
     * Get Valid Vendor IDs based on Product Search Query
     * Syncs what's visible in autocomplete vs result page
     * * @param string $query
     * @return array
     */
    public function getVendorIdsByProductQuery($query)
    {
        if (empty($query)) {
            return [];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('vendor_id');
        
        // Search in Name, SKU, and Description (consistent everywhere)
        $productCollection->addAttributeToFilter([
            ['attribute' => 'name', 'like' => "%$query%"],
            ['attribute' => 'sku', 'like' => "%$query%"],
            ['attribute' => 'description', 'like' => "%$query%"],
            ['attribute' => 'short_description', 'like' => "%$query%"]
        ]);

        $productCollection->addAttributeToFilter('vendor_id', ['notnull' => true]);

        $vendorIds = $productCollection->getColumnValues('vendor_id');
        return array_unique(array_filter($vendorIds));
    }

    /**
     * Utility to validate a specific set of IDs against the main filter rules
     * * @param array $vendorIds
     * @return array
     */
    public function getValidVendorIds(array $vendorIds)
    {
        if (empty($vendorIds)) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $vendorTable = $this->resource->getTableName('ves_vendor_entity');
        $membershipTable = $this->resource->getTableName('ves_vendor_membership_transaction');

        $select = $connection->select()
            ->from(['v' => $vendorTable], ['entity_id'])
            ->joinLeft(
                ['vmt' => $membershipTable],
                'v.entity_id = vmt.vendor_id',
                []
            )
            ->where('v.entity_id IN (?)', $vendorIds)
            ->where('v.status = ?', Vendor::STATUS_APPROVED)
            ->where("
                vmt.transaction_id IS NULL OR 
                DATE_ADD(vmt.created_at, INTERVAL vmt.duration MONTH) >= NOW()
            ")
            ->group('v.entity_id');
       
        return $connection->fetchCol($select);
    }
}