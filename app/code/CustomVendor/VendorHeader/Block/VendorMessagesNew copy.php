<?php
namespace CustomVendor\VendorHeader\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\App\ResourceConnection;

class VendorMessagesNew extends Template
{
    /**
     * @var VendorSession
     */
    protected $vendorSession;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param Context $context
     * @param VendorSession $vendorSession
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        Context $context,
        VendorSession $vendorSession,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->vendorSession = $vendorSession;
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    /**
 * Get vendor logo URL using custom attribute 'upload_logo'
 *
 * @return string
 */
public function getVendorLogoUrl()
{
    if (!$this->vendorSession->isLoggedIn()) {
        return '';
    }

    $vendor = $this->vendorSession->getVendor()->getData(); // fetch all vendor data
    $mediaUrl = $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "pub/media/";
    $defualt_logo="/wysiwyg/gc-notification.png";
    // Use custom attribute 'upload_logo', fallback to default logo
    $logoPath = !empty($vendor['upload_logo']) ? trim($vendor['upload_logo'], '/') : $defualt_logo;

    return $mediaUrl . $logoPath;
}

    /**
     * Get unread unique message count for logged-in vendor
     *
     * @return int
     */
    public function getUnreadMessageCount()
    {
        if (!$this->vendorSession->isLoggedIn()) {
            return 0;
        }

        $vendorId = $this->vendorSession->getVendor()->getId();

        try {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('ves_vendor_message_detail');

            $sql = "SELECT COUNT(*) AS unique_message_count
                    FROM (
                        SELECT DISTINCT subject, content
                        FROM {$tableName}
                        WHERE receiver_id = :vendor_id AND is_read = 0
                    ) AS t";

            $count = $connection->fetchOne($sql, ['vendor_id' => $vendorId]);

            return (int)$count;
        } catch (\Exception $e) {
            $this->_logger->error('Vendor Message Count Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get vendor messages page URL
     *
     * @return string
     */
    public function getMessagesUrl()
    {
        return $this->getUrl('vendors/message/index');
    }
}
