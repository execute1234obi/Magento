<?php
namespace Custom\HsCode\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StatusOptions implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Enabled')],
            ['value' => 0, 'label' => __('Disabled')],
        ];
    }
}
