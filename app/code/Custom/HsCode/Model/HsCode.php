<?php
namespace Custom\HsCode\Model;

use Magento\Framework\Model\AbstractModel;

class HsCode extends AbstractModel
{
    protected $_idFieldName = 'hscode_id'; // ✅ Important line
    protected function _construct()
    {
        $this->_init(\Custom\HsCode\Model\ResourceModel\HsCode::class);
    }

    
}
