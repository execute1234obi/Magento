<?php

namespace Vnecoms\VendorsMembership\Model\Source;

class ExpiryGroup extends \Vnecoms\Vendors\Model\Source\Group
{
    /**
     * @var  \Vnecoms\VendorsMembership\Helper\Data
     */
    protected $helper;
    
    public function __construct(
        \Vnecoms\VendorsMembership\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * (non-PHPdoc).
     *
     * @see \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface::getAllOptions()
     */
    public function getAllOptions($blankLine = false)
    {
        $options = parent::getAllOptions(false);
        
        $newOpts = [];
        foreach($options as $option){
            if($option['value'] == $this->helper->getDefaultVendorGroup()) continue;
            $newOpts[] = $option;
        }

        return $newOpts;
    }
}
