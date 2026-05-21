<?php

namespace Custom\SearchExtended\Plugin;

use Magento\Framework\App\RequestInterface;
use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory as VendorCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\Response\Builder as ResponseBuilder;

class SearchResponseCountPlugin
{
    protected $request;
    protected $vendorCollectionFactory;
    protected $resourceConnection;

    public function __construct(
        RequestInterface $request,
        VendorCollectionFactory $vendorCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->request = $request;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function afterBuild(ResponseBuilder $subject, $response)
    {
        $country = $this->request->getParam('svendor_country_id');
        
        // Agar koi country filter nahi hai toh standard chalne do
        if (!$country) {
            return $response;
        }

        // 1. Get Selected Country Vendor IDs
        $vendorCollection = $this->vendorCollectionFactory->create();
        $vendorCollection->addFieldToFilter('country_id', $country);
        $vendorIds = $vendorCollection->getAllIds();

        if (empty($vendorIds)) {
            $vendorIds = [0];
        }

        try {
            $reflection = new \ReflectionClass($response);
            
            // 2. Unlock Mirasvit documents property safely
            $documentsProp = $reflection->getProperty('documents');
            $documentsProp->setAccessible(true);
            $documents = $documentsProp->getValue($response);

            // 3. Direct DB Connection se un vendors ke saare valid product IDs nikal lo
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('catalog_product_entity');
            
            $select = $connection->select()
                ->from($tableName, ['entity_id'])
                ->where('vendor_id IN (?)', $vendorIds);
                
            $validDbProductIds = $connection->fetchCol($select);

            // 4. DATA INJECTION: Agar Mirasvit ke paas documents hain, toh filter karo
            // Agar Mirasvit ke paas nahi hain (jaise Argentina empty chunk), toh khud naya document inject karo
            $filteredDocuments = [];
            
            if (!empty($documents)) {
                foreach ($documents as $doc) {
                    if (in_array((int)$doc->getId(), $validDbProductIds)) {
                        $filteredDocuments[] = $doc;
                    }
                }
            }

            // Agar hamare paas DB mein products hain (jaise Product ID 98) par Mirasvit ke docs khali the
            if (empty($filteredDocuments) && !empty($validDbProductIds)) {
                // Mirasvit ke generic document factory class ko inject karne ke bajaye, 
                // hum direct existing document object clone karke IDs force alter kar dete hain
                
                // Ek dummy baseline layout document banate hain block bypass ke liye
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $docFactory = $om->get(\Magento\Framework\Search\DocumentFactory::class);
                
                foreach ($validDbProductIds as $pId) {
                    $filteredDocuments[] = $docFactory->create([
                        'data' => [
                            'id' => $pId,
                            'score' => 1.0000
                        ]
                    ]);
                }
            }

            // 5. Force Overwrite Response Array Data
            $documentsProp->setValue($response, $filteredDocuments);
            
            // 6. Force Overwrite Response Count Parameter
            $countProp = $reflection->getProperty('count');
            $countProp->setAccessible(true);
            $countProp->setValue($response, count($filteredDocuments));

        } catch (\Exception $e) {
            // Anti-crash fallback
        }

        return $response;
    }
}