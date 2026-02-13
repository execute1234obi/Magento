<?php
namespace Business\VendorVisitorReport\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class AggregateProfileVisitor
{
    protected $resource;
    protected $logger;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function execute()
    {
    file_put_contents(
        BP . '/var/log/vendor_cron_test.log',
        "CRON HIT at " . date('Y-m-d H:i:s') . PHP_EOL,
        FILE_APPEND
    );

    // existing code below

        $connection = $this->resource->getConnection();

        $rawTable = $this->resource->getTableName('business_vendor_profile_visitor');
        $aggTable = $this->resource->getTableName('business_vendor_mostview_aggregated');
        $countryTable = $this->resource->getTableName('business_visitorcountry_report_country');

//run properly only vendor code is same as vendor
       $sql = "
    INSERT INTO {$aggTable}
        (vendor_id, vendor_code, mastercountry_id, views_num, store_id, period)
    SELECT
        v.vendor_id,
        v.vendor_id AS vendor_code,
        c.id AS mastercountry_id,
        COUNT(*) AS views_num,
        1 AS store_id,
        DATE(v.visited_at) AS period
    FROM {$rawTable} v
    JOIN {$countryTable} c
        ON c.country_id = v.country_code
    GROUP BY
        v.vendor_id,
        c.id,
        DATE(v.visited_at)
    ON DUPLICATE KEY UPDATE
        views_num = VALUES(views_num)
";
        try {
            $connection->query($sql);
            $this->logger->info('Vendor Profile Visitor aggregation completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Aggregation failed: ' . $e->getMessage());
        }
    }
}
