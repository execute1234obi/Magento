<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Viewed Product Index Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Business\MostviewedVendors\Model\ResourceModel\Vendor\Index;


/**
 * @api
 * @since 100.0.2
 */
class Viewed extends \Magento\Reports\Model\ResourceModel\Product\Index\AbstractIndex
{
    /**
     * Initialize connection and main resource table
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('business_report_viewed_vendors_index', 'index_id');
    }
}
