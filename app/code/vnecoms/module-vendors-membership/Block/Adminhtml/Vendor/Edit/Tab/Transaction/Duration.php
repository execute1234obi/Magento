<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Block\Adminhtml\Vendor\Edit\Tab\Transaction;

use Magento\Framework\DataObject;
use Vnecoms\VendorsMembership\Model\Source\DurationUnit;

/**
 * Customer Credit transactions grid
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Duration extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Get duration label.
     *
     * @param int $duration
     * @param int $unit
     */
    public function getDurationLabel($duration, $unit)
    {
        $label = '';
        switch ($unit) {
            case DurationUnit::DURATION_DAY:
                $label = $duration == 1 ? __('%1 Day', $duration) : __('%1 Days', $duration);
                break;
            case DurationUnit::DURATION_WEEK:
                $label = $duration == 1 ? __('%1 Week', $duration) : __('%1 Weeks', $duration);
                break;
            case DurationUnit::DURATION_MONTH:
                $label = $duration == 1 ? __('%1 Month', $duration) : __('%1 Months', $duration);
                break;
            case DurationUnit::DURATION_YEAR:
                $label = $duration == 1 ? __('%1 Year', $duration) : __('%1 Years', $duration);
                break;
        }
    
        return $label;
    }
    
    /**
     * Renders grid column
     *
     * @param   Object $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        return $this->getDurationLabel(
            $row->getData($this->getColumn()->getIndex()),
            $row->getData('duration_unit')
        );
    }
}
