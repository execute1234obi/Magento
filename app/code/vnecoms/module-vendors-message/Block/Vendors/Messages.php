<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Vendors;

/**
 * Vendor Notifications block
 */
class Messages extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
    public function getTitle(){
        return $this->getData('block_title')?$this->getData('block_title'):__("Inbox");
    }
}
