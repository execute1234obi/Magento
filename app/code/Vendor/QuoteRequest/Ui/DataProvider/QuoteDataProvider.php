<?php
namespace Vendor\QuoteRequest\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\Grid\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

class QuoteDataProvider extends AbstractDataProvider
{
    protected $urlBuilder;
    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        HttpRequest $request,  
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Returns data for the grid with pagination
     */
    public function getData()
    {
        // Fix: Use $this->request to get the pagination params
        $pageSize = $this->request->getParam('page_size', 10);  // Default page size: 10
        $currentPage = $this->request->getParam('page', 1);       // Default page: 1

        // Set the current page and page size
        $this->collection->setPageSize($pageSize);
        $this->collection->setCurPage($currentPage);

        // Load collection with pagination applied
        if (!$this->collection->isLoaded()) {
            $this->collection->load();
        }

        // Get the data from the collection
        $items = $this->collection->getData();

        // Add actions to each item in the collection
        foreach ($items as &$item) {
            $item['actions'] = [
                'view' => [
                    'href' => $this->getViewUrl($item['quote_id']),
                    'label' => __('View')
                ]
            ];
        }

        // Return the paginated data
        return [
            'totalRecords' => $this->collection->getSize(),  // Total number of records (for pagination)
            'items' => $items  // The current page's items
        ];
    }

    /**
     * Returns the URL for the "view" action.
     */
    protected function getViewUrl($quoteId)
    {
        return $this->urlBuilder->getUrl(
            'vendorquotation/index/view',  // Your custom URL path for the view action
            ['quote_id' => $quoteId]       // URL parameter for quote_id
        );
    }
}