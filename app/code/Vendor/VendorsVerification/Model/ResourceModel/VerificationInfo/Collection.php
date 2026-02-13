<?php

namespace Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zend_Db_Select;

/**
 * Class Collection
 * @package Vendor\Advertisement\Model\ResourceModel\Adspace
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'detail_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'business_vendor_verificationinfo_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_verification_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vendor\VendorsVerification\Model\VerificationInfo', 'Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }   
}
