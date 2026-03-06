<?php

namespace Vnecoms\VendorsMembership\Model\Source;

class ExpiryAction extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const ACTION_CLOSE  = 'close';
    const ACTION_MOVE   = 'move';
    
    /**
     * @var array
     */
    protected $_options;


    /**
     * (non-PHPdoc).
     *
     * @see \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface::getAllOptions()
     */
    public function getAllOptions($blankLine = false)
    {
        $this->_options = [
            [
                'value' => self::ACTION_CLOSE,
                'label' => __("Set vendor status to Expired."),
            ],
            [
                'value' => self::ACTION_MOVE,
                'label' => __("Change vendor group to: "),
            ]
        ];

        return $this->_options;
    }

    /**
     * Retrieve option array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->toOptionArray() as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
