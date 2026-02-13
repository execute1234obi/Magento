<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Vnecoms\Vendors\Model\Vendor;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\App\ResourceConnection;


class Index extends AbstractIndex
{
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resource;

    private $collectionFactory;

    public function __construct(
    VendorCollectionFactory $collectionFactory,
    Context $context,
    ResourceConnection $resource
) {
    $this->collectionFactory = $collectionFactory;
    $this->resource = $resource;
    
    parent::__construct($context);
    file_put_contents(BP . '/var/log/vendor_index_debug.log', 'Index class loaded');
}


    public function getName(): string
    {
        return 'Vnecoms / Vendors';
    }

    public function getIdentifier(): string
    {
        return 'vnecoms_vendors';
    }

    public function getAttributes(): array
    {
        return [
            'business_name' => __('Business Name'),
        ];
    }

    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['business_name']);
        $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');

        return $collection;
    }
     public function getAttributeId(string $attributeCode): ?int
    {
        $connection = $this->resource->getConnection();
        $eavEntityTypeTable = $this->resource->getTableName('eav_entity_type');
        $eavAttributeTable = $this->resource->getTableName('eav_attribute');

        $select = $connection->select()
            ->from(['et' => $eavEntityTypeTable], [])
            ->join(['ea' => $eavAttributeTable], 'et.entity_type_id = ea.entity_type_id', ['attribute_id'])
            ->where('et.entity_type_code = ?', 'ves_vendor')
            ->where('ea.attribute_code = ?', $attributeCode)
            ->limit(1);

        return (int) $connection->fetchOne($select) ?: null;
    }

//     public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
//     {
//         // $collection = $this->collectionFactory->create()
//         //     ->addAttributeToSelect(['business_name'])
//         //     ->addAttributeToFilter('status', Vendor::STATUS_APPROVED);

//         // if ($entityIds) {
//         //     $collection->addFieldToFilter('e.entity_id', ['in' => $entityIds]);
//         // }

//         // $collection->addAttributeToFilter('entity_id', ['gt' => $lastEntityId])
//         //     ->setPageSize($limit)
//         //     ->setOrder('entity_id');

//         // return $collection->toArray();
//         $connection = $this->resource->getConnection();
//     $vendorTable = $this->resource->getTableName('ves_vendor_entity');
//     $vendorVarcharTable = $this->resource->getTableName('ves_vendor_entity_varchar');
//     $vendorTextTable = $this->resource->getTableName('ves_vendor_entity_text');

//     $attrVendorNameId = $this->getAttributeId('c_name');
//     $attrCategoryId = $this->getAttributeId('business_category');
//     $attrDescriptionId = $this->getAttributeId('business_description');
//     $attrCompanyNameId = $this->getAttributeId('company_name');
//     $attrVendorEmailId = $this->getAttributeId('vendor_email');

//     if (!$attrVendorNameId || !$attrDescriptionId) {
//         return []; // Fail-safe: don’t index if critical attributes missing

//     }
// }

public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
{
    $connection = $this->resource->getConnection();
    $vendorTable = $this->resource->getTableName('ves_vendor_entity');
    $vendorVarcharTable = $this->resource->getTableName('ves_vendor_entity_varchar');
    $vendorTextTable = $this->resource->getTableName('ves_vendor_entity_text');

    $attrVendorNameId = $this->getAttributeId('c_name');
    $attrCategoryId = $this->getAttributeId('business_category');
    $attrDescriptionId = $this->getAttributeId('business_description');
    $attrCompanyNameId = $this->getAttributeId('company_name');
    $attrVendorEmailId = $this->getAttributeId('vendor_email');

    if (!$attrVendorNameId || !$attrDescriptionId) {
        return [];
    }

    $sql = "
        SELECT 
            e.entity_id,
            name.value AS vendor_name,
            cat.value AS business_category,
            descr.value AS business_description,
            comp.value AS company_name,
            email.value AS vendor_email
        FROM {$vendorTable} AS e
        LEFT JOIN {$vendorVarcharTable} AS name
            ON e.entity_id = name.entity_id AND name.attribute_id = {$attrVendorNameId}
        LEFT JOIN {$vendorVarcharTable} AS cat
            ON e.entity_id = cat.entity_id AND cat.attribute_id = {$attrCategoryId}
        LEFT JOIN {$vendorTextTable} AS descr
            ON e.entity_id = descr.entity_id AND descr.attribute_id = {$attrDescriptionId}
        LEFT JOIN {$vendorVarcharTable} AS comp
            ON e.entity_id = comp.entity_id AND comp.attribute_id = {$attrCompanyNameId}
        LEFT JOIN {$vendorVarcharTable} AS email
            ON e.entity_id = email.entity_id AND email.attribute_id = {$attrVendorEmailId}
        LIMIT {$limit}
    ";

    $results = $connection->fetchAll($sql);
    $documents = [];

    foreach ($results as $row) {
        $documents[$row['entity_id']] = [
            'vendor_name' => $row['vendor_name'],
            'business_category' => $row['business_category'],
            'business_description' => $row['business_description'],
            'company_name' => $row['company_name'],
            'vendor_email' => $row['vendor_email'],
        ];
    }
    file_put_contents(BP . '/var/log/vendor_index_debug.log', print_r($documents, true));
    return $documents;
}
}
