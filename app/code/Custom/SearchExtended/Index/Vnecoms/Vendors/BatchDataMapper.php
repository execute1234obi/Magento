<?php

namespace Custom\SearchExtended\Index\Vnecoms\Vendors;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

class BatchDataMapper implements BatchDataMapperInterface
{
    /**
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
   public function map(array $documentData, $storeId, array $context = []): array
    {
        $result = [];
        
        foreach ($documentData as $id => $doc) {
            // Map the raw data to the correct attribute names
            // as defined in your Index.php file and the Mirasvit search index configuration
            $result[$id] = [
                'b_name'              => $doc['b_name'] ?? '',
                'business_descriptions' => $doc['business_descriptions'] ?? '',
                'c_name'              => $doc['c_name'] ?? '',
                // If you have more attributes defined in Index.php, add them here
            ];
        }

        return $result;
    }
}
