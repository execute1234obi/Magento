<?php

namespace Vnecoms\VendorsMembership\Model\Source;

class DurationUnit extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const DURATION_DAY = 1;
    const DURATION_WEEK = 2;
    const DURATION_MONTH = 3;
    const DURATION_YEAR = 4;

    /**
     * Options array.
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Retrieve all options array.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Day'), 'value' => self::DURATION_DAY],
                ['label' => __('Week'), 'value' => self::DURATION_WEEK],
                ['label' => __('Month'), 'value' => self::DURATION_MONTH],
                ['label' => __('Year'), 'value' => self::DURATION_YEAR],
            ];
        }

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
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }

    /**
     * Get options as array.
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
