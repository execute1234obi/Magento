<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Business\MostviewedVendors\Model;

class Flag extends \Magento\Framework\Flag
{
    
    const REPORT_VNE_VENDORS_VIEWED_FLAG_CODE =  'report_vnevendor_viewed_aggregated';

    /**
     * Setter for flag code
     * @codeCoverageIgnore
     *
     * @param string $code
     * @return $this
     */
    public function setReportFlagCode($code)
    {
        $this->_flagCode = $code;
        return $this;
    }
}
