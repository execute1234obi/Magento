<?php

namespace Business\MostviewedVendors\Model\ResourceModel\Vendor\Index\Collection;

abstract class AbstractCollection extends \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection
                                           
{
    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;
    
    protected $_customerSession;
    
    protected $_localeDate;
    
    protected $dateTime;

     public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\Visitor $customerVisitor        
    ) {        
        
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $connection
        );
        
        $this->_customerVisitor = $customerVisitor;
        $this->_customerSession = $customerSession;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
    }


   /* public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->_customerVisitor = $customerVisitor;
    }*/

    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    abstract protected function _getTableName();

    /**
     * Join index table
     *
     * @return $this
     */
    protected function _joinIdxTable()
    {
        if (!$this->getFlag('is_idx_table_joined')) {
            $this->joinTable(
                ['idx_table' => $this->_getTableName()],
                'vendor_id=entity_id',
                ['vendor_id' => 'vendor_id', 'item_store_id' => 'store_id', 'added_at' => 'added_at'],
                $this->_getWhereCondition()
            );
            $this->setFlag('is_idx_table_joined', true);
        }
        return $this;
    }

    /**
     * Add Viewed Products Index to Collection
     *
     * @return $this
     */
    public function addIndexFilter()
    {
        $this->_joinIdxTable();
        //$this->_productLimitationFilters['store_table'] = 'idx_table';
        //$this->setFlag('url_data_object', true);
        //$this->setFlag('do_not_use_category_id', true);
        return $this;
    }

    /**
     * Add filter by vendor ids
     *
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        if (empty($ids)) {
            $this->getSelect()->where('1=0');
        } else {
            $this->getSelect()->where('e.entity_id IN(?)', $ids);
        }
        return $this;
    }

    /**
     * Retrieve Where Condition to Index table
     *
     * @return array
     */
    protected function _getWhereCondition()
    {
        $condition = [];

        if ($this->_customerSession->isLoggedIn()) {
            $condition['customer_id'] = $this->_customerSession->getCustomerId();
        } elseif ($this->_customerId) {
            $condition['customer_id'] = $this->_customerId;
        } else {
            $condition['visitor_id'] = $this->_customerVisitor->getId();
        }

        return $condition;
    }

    /**
     * Set customer id, that will be used in 'whereCondition'.
     *
     * @codeCoverageIgnore
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id)
    {
        $this->_customerId = (int)$id;
        return $this;
    }

    /**
     * Add order by "added at"
     *
     * @param string $dir
     * @return $this
     */
    public function setAddedAtOrder($dir = self::SORT_ORDER_DESC)
    {
        if ($this->getFlag('is_idx_table_joined')) {
            $this->getSelect()->order('added_at ' . $dir);
        }
        return $this;
    }

    /**
     * Add exclude vendor Ids
     *
     * @param int|array $vendorIds
     * @return $this
     */
    public function excludeVendorIds($vendorIds)
    {
        if (empty($vendorIds)) {
            return $this;
        }
        $this->_joinIdxTable();
        $this->getSelect()->where('idx_table.vendor_id NOT IN(?)', $vendorIds);
        return $this;
    }
}
