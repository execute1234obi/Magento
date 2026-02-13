<?php
namespace Business\VisitorcountryReport\Model;

class Country extends \Magento\Framework\Model\AbstractModel
{
        
    

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'business_visitorcountryreport_country';
    
    /**
	* Define resource model
	*/
	protected function _construct()
	{			
			$this->_init('Business\VisitorcountryReport\Model\ResourceModel\Country');

	}
    
}

