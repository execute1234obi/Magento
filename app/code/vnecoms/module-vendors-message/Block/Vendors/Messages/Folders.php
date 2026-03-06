<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Vendors\Messages;

/**
 * Vendor Notifications block
 */
class Folders extends \Vnecoms\VendorsMessage\Block\Vendors\Toplinks\Messages
{
    /**
     * Get Inbox URL
     * 
     * @return string
     */
    public function getInboxURL(){
        return $this->getUrl("message");
    }
    
    /**
     * Get Out Box URL
     *
     * @return string
     */
    public function getOutboxURL(){
        return $this->getUrl("message/sent");
    }
    
    /**
     * Get Draft URL
     *
     * @return string
     */
    public function getDraftURL(){
        return $this->getUrl("message/draft");
    }
    
    /**
     * Get Trash URL
     *
     * @return string
     */
    public function getTrashURL(){
        return $this->getUrl("message/trash");
    }
    
    /**
     * Is Active Box
     * 
     * @param string $box
     * @return boolean
     */
    public function isActiveBox($box){
        return $box == $this->getActiveBox();
    }
}
