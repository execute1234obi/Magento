<?php

namespace Business\MostviewedVendors\Model\ResourceModel\Report;

/**
 * Order entity resource model
 */
class Vendor extends AbstractReport
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Order\CreatedatFactory
     */
    protected $_createDatFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Order\UpdatedatFactory
     */
    protected $_updateDatFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\ResourceModel\Report\Order\CreatedatFactory $createDatFactory
     * @param \Magento\Sales\Model\ResourceModel\Report\Order\UpdatedatFactory $updateDatFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,        
        \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\CreatedatFactory  $createDatFactory,
        \Business\MostviewedVendors\Model\ResourceModel\Report\Vendor\UpdatedatFactory $updateDatFactory,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->_createDatFactory = $createDatFactory;
        $this->_updateDatFactory = $updateDatFactory;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('business_vendor_mostview_aggregated', 'id');
    }

    /**
     * Aggregate Orders data
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_createDatFactory->create()->aggregate($from, $to);
        $this->_updateDatFactory->create()->aggregate($from, $to);
        $this->_setFlagData(\Business\MostviewedVendors\Model\Flag::REPORT_VNE_VENDORS_VIEWED_FLAG_CODE);
        return $this;
    }
}
