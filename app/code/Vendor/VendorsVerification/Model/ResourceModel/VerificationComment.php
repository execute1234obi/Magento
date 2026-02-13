<?php
namespace Vendor\VendorsVerification\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VerificationComment extends AbstractDb
{
    /**
	 * Define main table
	*/
	protected function _construct()
	{
	   $this->_init('business_vendor_verification_comments','comment_id');
	 }
	 
	 
}

