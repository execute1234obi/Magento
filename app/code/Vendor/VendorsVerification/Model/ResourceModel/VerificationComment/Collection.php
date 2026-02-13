<?php

namespace Vendor\VendorsVerification\Model\ResourceModel\VerificationComment;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zend_Db_Select;

/**
 * Class Collection
 * @package Vendor\VendorsVerification\Model\ResourceModel\VerificationComment
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'comment_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'Vendor_vendor_comments_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'business_vendor_comments_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vendor\VendorsVerification\Model\VerificationComment', 'Vendor\VendorsVerification\Model\ResourceModel\VerificationComment');
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
