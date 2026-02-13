<?php
namespace Magento\Fedex\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class PickupType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'REGULAR_PICKUP', 'label' => __('Regular Pickup')],
            ['value' => 'DROP_BOX', 'label' => __('Drop Box')],
            ['value' => 'REQUEST_COURIER', 'label' => __('Request Courier')],
            ['value' => 'BUSINESS_SERVICE_CENTER', 'label' => __('Business Service Center')],
            ['value' => 'STATION', 'label' => __('Station')],
        ];
    }
}
