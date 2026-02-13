<?php
// namespace Custom\HsCode\Ui\DataProvider;

// use Magento\Ui\DataProvider\AbstractDataProvider;
// use Custom\HsCode\Model\ResourceModel\HsCode\CollectionFactory;

// class HsCodeDataProvider extends AbstractDataProvider
// {
//     protected $collection;

//     public function __construct(
//         $name,
//         $primaryFieldName,
//         $requestFieldName,
//         CollectionFactory $collectionFactory,
//         array $meta = [],
//         array $data = []
//     ) {
//         $this->collection = $collectionFactory->create();

//         parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
//     }

//     public function getData()
//     {
//         $items = $this->collection->getItems();

//         return [
//             'totalRecords' => $this->collection->getSize(),
//             'items' => array_map(function ($item) {
//                 return $item->getData();
//             }, $items),
//         ];
//     }
// }
///////////
// namespace Custom\HsCode\Ui\DataProvider;

// use Magento\Ui\DataProvider\AbstractDataProvider;
// use Custom\HsCode\Model\ResourceModel\HsCode\CollectionFactory;

// class HsCodeDataProvider extends AbstractDataProvider
// {
//     protected $collection;

//     public function __construct(
//         $name,
//         $primaryFieldName,
//         $requestFieldName,
//         CollectionFactory $collectionFactory,
//         array $meta = [],
//         array $data = []
//     ) {
//         $this->collection = $collectionFactory->create();
//         parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
//     }

//     public function getData()
//     {
//           // Fetch the collection
//     $this->collection = $this->hsCodeCollectionFactory->create();
    
//     // Log collection size (total records)
//     $this->logger->debug("Total Records: " . $this->collection->getSize());
    
//     // Convert collection to array and log the items
//     $items = $this->collection->toArray()['items'];
    
//     // Log the actual data in items
//     $this->logger->debug("Fetched Items: " . print_r($items, true));
//         return [
//             'totalRecords' => $this->collection->getSize(),
//             'items' => $this->collection->toArray()['items'],
//         ];
//     }
// }

namespace Custom\HsCode\Ui\DataProvider;

use Custom\HsCode\Model\ResourceModel\HsCode\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Psr\Log\LoggerInterface;

class HsCodeDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $hsCodeCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * HsCodeDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $hsCodeCollectionFactory
     * @param LoggerInterface $logger
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $hsCodeCollectionFactory,  // Inject CollectionFactory here
        LoggerInterface $logger,  // Inject LoggerInterface here
        array $meta = [],
        array $data = []
    ) {
        // Initialize the parent class constructor
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        // Set the injected collection factory and logger
        $this->hsCodeCollectionFactory = $hsCodeCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Get data for grid or other data providers
     *
     * @return array
     */
    public function getData()
    {
        $collection = $this->hsCodeCollectionFactory->create();

        // Log the collection size and data for debugging
        $this->logger->debug('Total Records: ' . $collection->getSize());
        $this->logger->debug('Fetched Items: ' . print_r($collection->toArray()['items'], true));

        return [
            'totalRecords' => $collection->getSize(),
            'items' => $collection->toArray()['items'],
        ];
    }
}
