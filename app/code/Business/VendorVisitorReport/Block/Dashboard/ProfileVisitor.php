<?php
namespace Business\VendorVisitorReport\Block\Dashboard;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;
//use Magento\Customer\Model\Session;
use Vnecoms\Vendors\Model\Session as VendorSession;


class ProfileVisitor extends Template
{
    protected $resource;
    //protected $customerSession;
    protected $vendorSession;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
       // Session $customerSession,
       VendorSession $vendorSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resource = $resource;
        //$this->customerSession = $customerSession;
        $this->vendorSession = $vendorSession;
    }

    public function getTotalVisitors()
    {
       $vendor = $this->vendorSession->getVendor();

if (!$vendor || !$vendor->getId()) {
    return 0;
}

$vendorId = (int)$vendor->getId();


        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('business_vendor_profile_visitor');

        $select = $connection->select()
            ->from($table, ['total' => 'COUNT(*)'])
            ->where('vendor_id = ?', $vendorId);
        // DEBUG: SQL dekhna ho to
        //echo $select->__toString(); die();

        return (int)$connection->fetchOne($select);
    }
}
