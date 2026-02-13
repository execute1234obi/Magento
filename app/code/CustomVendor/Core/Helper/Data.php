<?php
namespace CustomVendor\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Get clean base URL (no store code like /default/ or /ar/)
     */
    public function getCleanBaseUrl()
    {
        $store = $this->storeManager->getStore();
        $baseWebUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $parsed = parse_url($baseWebUrl);

        return $parsed['scheme'] . '://' . $parsed['host'] . '/';
    }

    /**
     * Get clean media URL (always without store code)
     */
    public function getCleanMediaUrl()
    {
        $rootUrl = $this->getCleanBaseUrl();
        return $rootUrl . 'pub/media/';
    }
}
