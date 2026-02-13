<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Vnecoms\Vendors\Model\Vendor;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

use Magento\Framework\DB\Select;
use Magento\Framework\App\RequestInterface; 
class Index extends AbstractIndex
{
    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resource;
    protected LoggerInterface $logger;
    private $collectionFactory;
     private RequestInterface $request; 
    public function __construct(
    LoggerInterface $logger,
    VendorCollectionFactory $collectionFactory,
    Context $context,
    ResourceConnection $resource,
      RequestInterface $request
   ) {
    $this->collectionFactory = $collectionFactory;
    $this->resource = $resource;
    $this->logger = $logger;
    $this->request = $request;

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
        'b_name' => __('Business Name'),
        'business_descriptions' => __('Business Description'),
        'country_id' => __('Country'),
        'c_name' => __('Contact Name'),
        'b_email' => __('Business Email'),
    ];
    //  return [
    //     'b_name' => __('Business Name'),
    // ];


    }

    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    public function buildSearchCollection(): Collection
    {
       //$collection = $this->collectionFactory->create();
//         $collection->addAttributeToSelect([
//        'business_descriptions',
//         'b_name',
//         'b_email',
//         'c_name'
// ]);
     //$collection->addAttributeToSelect(['b_name']);
    //$collection->addAttributeToFilter('status', Vendor::STATUS_APPROVED);
      //  $matchedIds = $this->context->getSearcher()->getMatchedIds();
// if (!empty($matchedIds)) {
//     $this->context->getSearcher()->joinMatches($collection, 'entity_id');
// }

   // No aliasing needed
   // $this->context->getSearcher()->joinMatches($collection, 'entity_id');

        
        //$this->logager->info("buildSearchCollection SQL =".$collection->getSelect());
        //file_put_contents(BP . '/var/log/vendor_index_sql.log', $collection->getSelect()->__toString());

        //return $collection;

        $collection = $this->collectionFactory->create();

        // Add attributes that you want to be available in the search results
        $collection->addAttributeToSelect([
            'c_name',
            'business_descriptions',
            'b_name'
        ]);
        $collection->addAttributeToFilter('status', Vendor::STATUS_APPROVED);

        // Get the search query from Mirasvit's searcher context
         // Get the search query from the Request object
        $query = $this->request->getParam('q');

        if ($query) {
            $collection->addAttributeToFilter([
                ['attribute' => 'c_name', 'like' => '%' . $query . '%'],
                ['attribute' => 'business_descriptions', 'like' => '%' . $query . '%'],
                ['attribute' => 'b_name', 'like' => '%' . $query . '%']
            ]);
        }
        // To ensure the collection is populated for the tab count
         // Log the number of results to a file for debugging
    file_put_contents(BP . '/var/log/vendor_collection_size.log', 'Collection Size: ' . $collection->getSize() . PHP_EOL, FILE_APPEND);
        $collection->load();
        return $collection;
    }
     public function getAttributeId(string $attributeCode): ?int
    {
    $connection = $this->resource->getConnection();
    $eavEntityTypeTable = $this->resource->getTableName('eav_entity_type');
    $eavAttributeTable = $this->resource->getTableName('eav_attribute');

    $select = $connection->select()
        ->from(['et' => $eavEntityTypeTable], [])
        ->join(
            ['ea' => $eavAttributeTable],
            'et.entity_type_id = ea.entity_type_id',
            ['attribute_id']
        )
        ->where('et.entity_type_code = ?', 'ves_vendor')
        ->where('ea.attribute_code = ?', $attributeCode)
        ->limit(1);

    $attributeId = $connection->fetchOne($select);
    
    return $attributeId !== false ? (int) $attributeId : null;
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

public function getIndexableDocuments(
    int $storeId,
    array $entityIds = null,
    int $lastEntityId = null,
    int $limit = 100
): array {
    $collection = $this->collectionFactory->create()
        ->addAttributeToSelect([
            'business_descriptions',
            'b_name',
            'b_email',
            'c_name'
        ])
        ->addAttributeToFilter('status', Vendor::STATUS_APPROVED);

    // Map 'entity_id' filter to 'main_table.entity_id' to avoid ambiguity in SQL
    $collection->addFilterToMap('entity_id', 'main_table.entity_id');

    if (!empty($entityIds)) {
        // If specific entity IDs are provided, use them directly
        $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
    } elseif ($lastEntityId !== null) {
        // If no entityIds but a lastEntityId is provided, paginate from there
        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
                   ->setPageSize($limit)
                   ->setOrder('main_table.entity_id', 'ASC');
    } else {
        // No filters—fetch from beginning
        $collection->setPageSize($limit)
                   ->setOrder('main_table.entity_id', 'ASC');
    }
   
    // Log the SQL for debugging
    // file_put_contents(BP . '/var/log/vendor_index_sql.log', $collection->getSelect()->__toString());
    // $this->logger->debug('Vendor collection data: ' . print_r($collection->toArray(), true));
      $documents = [];
    foreach ($collection as $vendor) {
        // $document = [
        //     'entity_id' => $vendor->getId(),
        //     'vendor_name' => $vendor->getData('b_name'), // Map to business name
        //     'description' => $vendor->getData('business_descriptions'), // Map to business description
        //     'associated_product' => '', // You'll need to implement logic to get associated products
        //     'mst_score_sum' => 0,
        //     'mst_score_multiply' => 1,
        // ];
        $document = ['entity_id' => $vendor->getId(),
            'b_name' => $vendor->getData('b_name'),
            'mst_score_sum' => 0,
            'mst_score_multiply' => 1,];

          // Log the document data to a custom file (INSIDE the loop)
        file_put_contents(BP . '/var/log/vendor_index_debug.log', json_encode($document) . PHP_EOL, FILE_APPEND);
        $documents[] = $document;
    }
     
    return $documents;

}

}
