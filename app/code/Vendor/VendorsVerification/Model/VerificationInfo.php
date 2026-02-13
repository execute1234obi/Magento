<?php

namespace Vendor\VendorsVerification\Model;


class VerificationInfo extends \Magento\Framework\Model\AbstractModel
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
    protected $_eventPrefix = 'Vendor_vendor_verification_info';
    
    /**
	* Define resource model
	*/
	protected function _construct()
	{
			$this->_init('Vendor\VendorsVerification\Model\ResourceModel\VerificationInfo');

	}
    
}
