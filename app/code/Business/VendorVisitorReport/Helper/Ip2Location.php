<?php
namespace Business\VendorVisitorReport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Ip2Location extends AbstractHelper
{
    const IP2LOCATION_COUNTRY_TABLE = 'ip_country';

    protected $resourceConnection;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

public function getLocationCountry()
{
    $ip = $this->getIp();
    $ipno = ip2long($ip);

    if (!$ipno) {
        return null;
    }

    $connection = $this->resourceConnection->getConnection();
    $table = $connection->getTableName(self::IP2LOCATION_COUNTRY_TABLE);

    $select = $connection->select()
        ->from($table, ['country_code'])
        ->where("$ipno BETWEEN ip_from AND ip_to")
        ->limit(1);

    return $connection->fetchOne($select) ?: null;
}

}
