<?php

namespace Vendor\VendorsVerification\Model;


class VendorVerification extends \Magento\Framework\Model\AbstractModel
{
        
    const STATUS_EXPIRED    = 9;    
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    
    

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'Vendor_vendor_verification';
    
    /**
	* Define resource model
	*/
	protected function _construct()
	{
			$this->_init('Vendor\VendorsVerification\Model\ResourceModel\VendorVerification');

	}
    
}
