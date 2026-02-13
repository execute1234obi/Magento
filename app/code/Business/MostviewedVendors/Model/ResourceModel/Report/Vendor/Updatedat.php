<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\MostviewedVendors\Model\ResourceModel\Report\Vendor;

/**
 * Order entity resource model with aggregation by updated at
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Updatedat extends Createdat
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('business_vendor_mostview_aggregated', 'id');
    }

    /**
     * Aggregate Orders data by order updated at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     */
    /*public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByField('added_at', $from, $to);
    }*/
    /*public function aggregate($from = null, $to = null)
    {
        return parent::aggregate($from, $to);
    }*/
}
