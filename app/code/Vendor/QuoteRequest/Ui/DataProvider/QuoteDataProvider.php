<?php
namespace Vendor\QuoteRequest\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
//use Vendor\QuoteRequest\Model\ResourceModel\Quote\CollectionFactory;
use Vendor\QuoteRequest\Model\ResourceModel\Quote\Grid\CollectionFactory;
use Magento\Framework\UrlInterface;
class QuoteDataProvider extends AbstractDataProvider
{
    protected $urlBuilder;
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
         UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
         $this->urlBuilder = $urlBuilder;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

// 
public function getData()
{
    if (!$this->collection->isLoaded()) {
        $this->collection->load();
    }

    $items = $this->collection->getData();

    foreach ($items as &$item) {

        $item['actions'] = [
            'view' => [
                'href' => $this->getViewUrl($item['quote_id']),
                'label' => __('View')
            ]
        ];
    }

    return [
        'totalRecords' => $this->collection->getSize(),
        'items' => $items
    ];
}
protected function getViewUrl($quoteId)
{
    return $this->urlBuilder->getUrl(
        'vendorquotation/index/view',   // ✅ FIXED
        ['quote_id' => $quoteId]
    );
}
}