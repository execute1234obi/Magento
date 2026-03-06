<?php
/**
 * Created by PhpStorm.
 * User: nvhai
 * Date: 12/23/2016
 * Time: 11:13 AM
 */
namespace Vnecoms\VendorsMessage\Ui\Component\Detail;

use Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail\CollectionFactory;
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
        $this->collection->getSelect()->joinInner(['msg' =>$this->collection->getTable('ves_vendor_message')], " msg.message_id = main_table.message_id AND msg.owner_id = main_table.sender_id", ['status','owner_id']);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

}
