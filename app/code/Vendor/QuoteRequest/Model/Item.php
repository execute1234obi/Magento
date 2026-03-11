<?php
namespace Vendor\QuoteRequest\Model;

class Item extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        // Yahan bhi ResourceModel ka path sahi karein
        $this->_init(\Vendor\QuoteRequest\Model\ResourceModel\Item::class);
    }
}