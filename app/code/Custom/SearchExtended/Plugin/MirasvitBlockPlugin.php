<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Mirasvit\Search\Block\Index\Base as MirasvitBaseBlock;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory as CatalogSearchCollectionFactory;

class MirasvitBlockPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CatalogSearchCollectionFactory
     */
    protected $catalogSearchCollectionFactory;

    /**
     * @param RequestInterface $request
     * @param CatalogSearchCollectionFactory $catalogSearchCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        CatalogSearchCollectionFactory $catalogSearchCollectionFactory
    ) {
        $this->request = $request;
        $this->catalogSearchCollectionFactory = $catalogSearchCollectionFactory;
    }

    /**
     * Overwrite Mirasvit block collection to force layout rendering
     */
    public function afterGetCollection(MirasvitBaseBlock $subject, $result)
    {
        $country      = $this->request->getParam('svendor_country_id');
        $verified     = $this->request->getParam('svendor_is_verified');
        $businessType = $this->request->getParam('svendor_business_type');

        // Agar koi filter nahi hai toh normal chalne do
        if (!$country && !$verified && !$businessType) {
            return $result;
        }

        // Check if current Mirasvit index code belongs to catalog product
        if ($subject->getIndex() && $subject->getIndex()->getIdentifier() === 'catalogsearch_fulltext') {
            
            // Create a fresh fulltext catalog search collection
            $freshCollection = $this->catalogSearchCollectionFactory->create();
            
            // This triggers our 'ProductCollectionPlugin' vendor SQL constraints automatically
            $freshCollection->load(); 

            if ($freshCollection->getSize() > 0) {
                // Force size parameter to prevent "Please select other tab" layout message
                $subject->setCollection($freshCollection);
                return $freshCollection;
            }
        }

        return $result;
    }
}