<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Search\Adapter\Mysql\Response\Builder as ResponseBuilder;

class SearchResponseCountPlugin
{
    protected $request;
    protected $vendorCollectionFactory;
    protected $productCollectionFactory;

    public function __construct(
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Intercept Mirasvit Search Engine raw output to fix global counters
     */
    public function afterBuild(ResponseBuilder $subject, $response)
    {
        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        if (!$country && !$verified && !$businessType) {
            return $response;
        }

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

        try {
            // Get original search documents returned by Elasticsearch/Mirasvit
            $reflection = new \ReflectionClass($response);
            $documentsProp = $reflection->getProperty('documents');
            $documentsProp->setAccessible(true);
            $documents = $documentsProp->getValue($response);

            if (empty($documents)) {
                return $response;
            }

            $searchIds = [];
            foreach ($documents as $doc) {
                $searchIds[] = (int)$doc->getId();
            }

            // Find matching valid items
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter('entity_id', ['in' => $searchIds]);
            $productCollection->getSelect()->where('e.vendor_id IN (?)', $vendorIds);
            $validIds = $productCollection->getAllIds();

            $filteredDocuments = [];
            foreach ($documents as $doc) {
                if (in_array((int)$doc->getId(), $validIds)) {
                    $filteredDocuments[] = $doc;
                }
            }

            // Override original data rows and counters directly inside object
            $documentsProp->setValue($response, $filteredDocuments);
            
            $countProp = $reflection->getProperty('count');
            $countProp->setAccessible(true);
            $countProp->setValue($response, count($filteredDocuments));

        } catch (\Exception $e) {
            // Safe fallback
        }

        return $response;
    }
}