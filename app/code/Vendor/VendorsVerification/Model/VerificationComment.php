<?php

namespace Vendor\VendorsVerification\Model;


class VerificationComment extends \Magento\Framework\Model\AbstractModel
{
        
    
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    
    

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'Vendor_vendor_comments';
    
    /**
	* Define resource model
	*/
	protected function _construct()
	{
			$this->_init('Vendor\VendorsVerification\Model\ResourceModel\VerificationComment');

	}
    
}
