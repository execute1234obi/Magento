<?php
namespace CustomVendor\VendorHeader\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;

class CustomerMessagesNew extends Template
{
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
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->resource = $resource;
        parent::__construct($context, $data);
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
            $tableName = $this->resource->getTableName('ves_vendor_message_detail');

            // Fetch unread message count for this customer (receiver)
            $sql = "SELECT COUNT(*) AS unread_count
                    FROM {$tableName}
                    WHERE receiver_id = :customer_id AND is_read = 0";

            $count = $connection->fetchOne($sql, ['customer_id' => $customerId]);
            return (int)$count;
        } catch (\Exception $e) {
            $this->_logger->error('Customer Message Count Error: ' . $e->getMessage());
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
        return 'http://gccapp.duckdns.org/customer/message/'; // your exact page URL
    }
}
