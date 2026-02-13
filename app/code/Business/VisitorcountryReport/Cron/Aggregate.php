<?php
namespace Business\VisitorcountryReport\Cron;

class Aggregate
{
    protected $createdat;

    public function __construct(
        \Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry\Createdat $createdat
    ) {
        $this->createdat = $createdat;
    }

    public function execute()
    {
        try {
            $this->createdat->aggregate();
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)
                ->error($e->getMessage());
        }
    }
}
