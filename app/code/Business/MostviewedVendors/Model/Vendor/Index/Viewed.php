<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\MostviewedVendors\Model\Vendor\Index;


class Viewed extends \Business\MostviewedVendors\Model\Vendor\Index\AbstractIndex
{
    /**
     * Cache key name for Count of product index
     *
     * @var string
     */
    protected $_countCacheKey = 'ves_vendor_profile_index_viewed_count';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Business\MostviewedVendors\Model\ResourceModel\Vendor\Index\Viewed::class);
    }

    /**
     * Retrieve Exclude Product Ids List for Collection
     *
     * @return array
     */
    public function getExcludeVendorIds()
    {
        $vendorIds = [];

        if ($this->_registry->registry('vendor')) {
            $vendorIds[] = $this->_registry->registry('vendor')->getId();
        }

        return $vendorIds;
    }
}
