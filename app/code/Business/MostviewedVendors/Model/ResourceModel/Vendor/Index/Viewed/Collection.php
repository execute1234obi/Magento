<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Viewed Product Index Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Business\MostviewedVendors\Model\ResourceModel\Vendor\Index\Viewed;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Business\MostviewedVendors\Model\ResourceModel\Vendor\Index\Collection\AbstractCollection

{
    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    protected function _getTableName()
    {
        return $this->getTable('business_report_viewed_vendors_index');
    }
}
