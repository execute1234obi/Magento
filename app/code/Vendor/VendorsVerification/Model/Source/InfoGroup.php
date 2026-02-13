<?php

namespace Vendor\VendorsVerification\Model\Source;

use Magento\Framework\DB\Ddl\Table;

class InfoGroup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    const VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION    = 1;    
    const VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS    = 2;    
    const VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT    = 3;    
    const VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS    = 4;    
    const VENDOR_VERIFICATION_DATAGROUP_ACTIVITY    = 5;    
    
    
    /**4
     * Options array
     *
     * @var array
     */
    protected $_options = null;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Business Information'), 'value' => self::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_INFORMATION],
                ['label' => __('Business Address'), 'value' => self::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_ADDRESS],
                ['label' => __('Business Contact'), 'value' => self::VENDOR_VERIFICATION_DATAGROUP_BUSINESS_CONTACT],
                ['label' => __('Business Certificates and Docs'), 'value' => self::VENDOR_VERIFICATION_DATAGROUP_CERTI_DOCS],
                ['label' => __('Vendor Activity'), 'value' => self::VENDOR_VERIFICATION_DATAGROUP_ACTIVITY],
            ];
        }
        return $this->_options;
    }    

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    
    
    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
    

    
}
