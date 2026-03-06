<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMembership\Model\Source;

/**
 * Source model for DHL Content Type
 */
class CodMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Payment\Helper\Data $paymentHelper
     */
    protected $_paymentHelper;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        $this->_paymentHelper = $paymentHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $data = [];
        $methodList = $this->_paymentHelper->getPaymentMethodList();
        foreach ($methodList as $key => $value) {
            $data[] =   ['label' => $value, 'value' => $key];
        }
        return $data;
    }
}
