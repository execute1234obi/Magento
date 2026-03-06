<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Ui\Component\Listing\Column;

use Vnecoms\VendorsMembership\Model\Source\DurationUnit;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Price
 */
class Duration extends Column
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
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->getDurationLabel($item[$this->getData('name')], $item['duration_unit']);
            }
        }

        return $dataSource;
    }
}
