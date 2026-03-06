<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Model\Source;

/**
 * @api
 * @since 100.0.2
 */
class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => 'Don\'t allow to send message'],
            ['value' => '1', 'label' => 'Log to warning list'],
        ];
    }
}
