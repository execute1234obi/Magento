<?php
namespace Custom\HsCode\Model\ResourceModel\HsCode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Custom\HsCode\Model\HsCode;
use Custom\HsCode\Model\ResourceModel\HsCode as HsCodeResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(HsCode::class, HsCodeResource::class);
    }
}
