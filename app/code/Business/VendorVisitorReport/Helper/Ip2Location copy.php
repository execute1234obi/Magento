<?php
namespace Business\VendorVisitorReport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Ip2Location extends AbstractHelper
{
    protected $remoteAddress;

    /**
     * Constructor
     *
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        RemoteAddress $remoteAddress
    ) {
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Get visitor IP address
     *
     * @return string
     */
    public function getVisitorIp()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Example function to return location info
     *
     * You can integrate real IP2Location API here later
     *
     * @param string|null $ip
     * @return array
     */
    public function getLocation($ip = null)
    {
        $ip = $ip ?: $this->getVisitorIp();
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
        ];
    }
}
