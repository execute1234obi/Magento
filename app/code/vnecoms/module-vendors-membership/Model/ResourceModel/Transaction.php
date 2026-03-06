<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMembership\Model\ResourceModel;

/**
 * Cms page mysql resource.
 */
class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_membership_transaction', 'transaction_id');
    }
}
