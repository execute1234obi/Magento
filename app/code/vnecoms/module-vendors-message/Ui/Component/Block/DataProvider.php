<?php
/**
 * Created by PhpStorm.
 * User: nvhai
 * Date: 12/23/2016
 * Time: 11:13 AM
 */
namespace Vnecoms\VendorsMessage\Ui\Component\Block;

use Vnecoms\VendorsMessage\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Vnecoms\RMA\Model\ResourceModel\Reponse\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $gatewayCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $reponseCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $reponseCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->collection->getSelect()->joinLeft($this->collection->getTable('customer_entity'), "entity_id=owner_id", ['firstname'=>'firstname','lastname'=>'lastname','middlename'=>'middlename','email'=>'email']);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

}
