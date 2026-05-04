<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Vnecoms\Vendors\Model\Vendor;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface; 
use Magento\Store\Model\StoreManagerInterface;
use Custom\SearchExtended\Model\VendorFilter;

class Index extends AbstractIndex
{
    protected $resource;
    protected LoggerInterface $logger;
    private $collectionFactory;
    private RequestInterface $request; 
    private UrlInterface $urlBuilder;
    private StoreManagerInterface $storeManager;
    private $vendorFilter;

    public function __construct(
        LoggerInterface $logger,
        VendorCollectionFactory $collectionFactory,
        Context $context,
        ResourceConnection $resource,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        VendorFilter $vendorFilter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager; 
        $this->vendorFilter = $vendorFilter;
        parent::__construct($context);
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
    }

    public function getPrimaryKey(): string
    {
        return 'entity_id';
    }

    /**
     * Build collection for the search engine
     */
    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create();

        // 1. Apply centralized status/membership rules
        $this->vendorFilter->apply($collection);

        $collection->addAttributeToSelect([
            'c_name',
            'business_descriptions',
            'b_name'
        ]);

        $query = trim((string)$this->request->getParam('q'));

        if ($query) {
            // 2. Use Centralized Product Search Logic
            $productVendorIds = $this->vendorFilter->getVendorIdsByProductQuery($query);

            $filters = [
                ['attribute' => 'c_name', 'like' => "%$query%"],
                ['attribute' => 'business_descriptions', 'like' => "%$query%"],
                ['attribute' => 'b_name', 'like' => "%$query%"],
            ];

            if (!empty($productVendorIds)) {
                $filters[] = ['attribute' => 'entity_id', 'in' => $productVendorIds];
            }

            $collection->addAttributeToFilter($filters);
        }

        return $collection;
    }

    /**
     * Document mapping for Mirasvit indexing
     */
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
                'c_name',
                'vendor_id',
                'upload_logo'
            ]);

        // Apply centralized filters
        $this->vendorFilter->apply($collection);
        
        $collection->addFilterToMap('entity_id', 'main_table.entity_id');

        if (!empty($entityIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        } elseif ($lastEntityId !== null) {
            $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
                       ->setPageSize($limit)
                       ->setOrder('main_table.entity_id', 'ASC');
        } else {
            $collection->setPageSize($limit)
                       ->setOrder('main_table.entity_id', 'ASC');
        }

        $documents = [];
        $baseMediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        foreach ($collection as $vendor) {
            $logo = trim((string) $vendor->getData('upload_logo'));
            $fullLogoUrl = $logo !== '' ? $baseMediaUrl . ltrim($logo, '/') : null;

            $url = $this->urlBuilder->getUrl(
                'shop/' . $vendor->getData('vendor_id'),
                ['_store' => $storeId]
            );

            $documents[] = [
                'entity_id'          => $vendor->getId(),
                'name'               => $vendor->getData('b_name') ?: $vendor->getData('c_name'),
                'upload_logo'        => $fullLogoUrl,
                'url'                => $url,
                'description'        => $vendor->getData('business_descriptions'),
                'mst_score_sum'      => 0,
                'mst_score_multiply' => 1,
            ];
        }

        return $documents;
    }
}
