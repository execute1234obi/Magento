<?php
namespace Business\VisitorcountryReport\Model;

class Flag extends \Magento\Framework\Flag
{
    //const REPORT_VISITORCOUNTRY_VISITED_FLAG_CODE =  'report_visitorcountry_visited_aggregated';
    const REPORT_VISITORCOUNTRY_VISITED_FLAG_CODE = 'report_visitorcountry_visited_aggregated';

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
        die("Test Reach");
        return $this;
    }
}
