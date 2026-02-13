<?php
namespace Vendor\VendorsVerification\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

//class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
class Status implements OptionSourceInterface
{

    const VENDOR_VERIFICATION_STATUS_PENDING    = 1;    
    const VENDOR_VERIFICATION_STATUS__RESUBMIT  = 2;
    const VENDOR_VERIFICATION_STATUS__REJECTED  = 3;
    const VENDOR_VERIFICATION_STATUS__VERIFIED   = 4;
    const VENDOR_VERIFICATION_STATUS_ENTIRE_REJECTED   = 5;
    
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
                ['label' => __('Pending'), 'value' => self::VENDOR_VERIFICATION_STATUS_PENDING],
                ['label' => __('Resubmit'), 'value' => self::VENDOR_VERIFICATION_STATUS__RESUBMIT],
                ['label' => __('Rejected'), 'value' => self::VENDOR_VERIFICATION_STATUS__REJECTED],
                ['label' => __('Approved'), 'value' => self::VENDOR_VERIFICATION_STATUS__VERIFIED], 
                 ['label' => __('Entire Registration Rejected'), 'value' => self::VENDOR_VERIFICATION_STATUS_ENTIRE_REJECTED],                            
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
