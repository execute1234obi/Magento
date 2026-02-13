<?php
namespace CustomVendor\VendorHeader\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\App\ResourceConnection;

class VendorMessagesNew extends Template
{
    /**
     * @var VendorSession
     */
    protected $vendorSession;
    protected $helper;

    /**
     * @var ResourceConnection
     */
     /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        VendorSession $vendorSession,        
        ResourceConnection $resource,
         \CustomVendor\Core\Helper\Data $helper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->vendorSession = $vendorSession;
        $this->resource = $resource;
         $this->urlBuilder = $context->getUrlBuilder(); // inject URL builder
            $this->helper = $helper;
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
    
    //$mediaUrl = $this->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "pub/media/";
       $mediaUrl = $this->helper->getCleanMediaUrl();
    $defualt_logo="/wysiwyg/nousericon1.png";
    // Use custom attribute 'upload_logo', fallback to default logo
    $logoPath = !empty($vendor['upload_logo']) ? trim($vendor['upload_logo'], '/') : $defualt_logo;

    return $mediaUrl . $logoPath;
}
 /**
     * Get Vendor URL based on user type
     *
     * @return string
     */
    public function getVendorUrl()
    {
        $vendorId = $this->vendorSession->getVendorId();
if ($vendorId) {
    // it's a vendor
    return $this->urlBuilder->getUrl('vendors/dashboard');
} else {
    // regular customer
    return $this->urlBuilder->getUrl('marketplace/seller/register/');
}
    }

    /**
     * Get unread message count for logged-in customer
     *
     * @return int
     */
    public function getUnreadMessageCount()
    {
         if (!$this->customerSession->isLoggedIn()) {
        return 0;
    }

    $customerId = $this->customerSession->getCustomerId();

    try {
        $connection = $this->resource->getConnection();
        $messageTable = $this->resource->getTableName('ves_vendor_message');
        $detailTable = $this->resource->getTableName('ves_vendor_message_detail');

        // Ye query same logic follow karti hai jo Count.php me use hua tha
        $sql = "
            SELECT COUNT(*) AS unread_count
            FROM {$messageTable} AS msg
            INNER JOIN {$detailTable} AS det ON msg.message_id = det.message_id
            WHERE det.receiver_id = :customer_id
              AND det.is_read = 0
              AND msg.is_inbox = 1
              AND msg.is_deleted = 0
        ";

        $count = (int)$connection->fetchOne($sql, ['customer_id' => $customerId]);
        return $count;

    } catch (\Exception $e) {
        $this->_logger->error('Unread Message Count Error: ' . $e->getMessage());
        return 0;
    }
    }

    /**
     * Get customer messages page URL
     *
     * @return string
     */
    public function getMessagesUrl()
    {
        return $this->getUrl('customer/message'); 
    }

}
