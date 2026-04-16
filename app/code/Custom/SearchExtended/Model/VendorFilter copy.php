<?php

namespace Custom\SearchExtended\Model;

use Magento\Framework\App\ResourceConnection;
use Vnecoms\Vendors\Model\Vendor;

class VendorFilter
{
    protected $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Apply common filters to vendor collection
     */
    public function apply($collection)
{
    $select = $collection->getSelect();
    $from   = $select->getPart(\Zend_Db_Select::FROM);

   // ✅ 1. Approved vendors
    $collection->addAttributeToFilter(
        'status',
        \Vnecoms\Vendors\Model\Vendor::STATUS_APPROVED
    );

    // ✅ 2. Check if already joined
    if (!isset($from['vmt'])) {

        $table = $this->resource->getTableName('ves_vendor_membership_transaction');

        $select->joinLeft(
            ['vmt' => $table],
            'e.entity_id = vmt.vendor_id',
            []
        );

        // latest transaction
        $select->where("vmt.transaction_id IN (
            SELECT MAX(t.transaction_id)
            FROM {$table} t
            GROUP BY t.vendor_id
        )");

        // active OR no membership
        // $select->where(
        //     "(vmt.transaction_id IS NULL OR DATE_ADD(vmt.created_at, INTERVAL vmt.duration MONTH) >= NOW())"
        // );
   }

    // ✅ 3. avoid duplicate rows
    $select->group('e.entity_id');
    //echo $collection->getSelect(); exit;
    return $collection;
}
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