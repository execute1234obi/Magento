<?php

namespace Vendor\VendorsVerification\Model\Source;

use Magento\Framework\DB\Ddl\Table;

class Countries extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	  
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;
    
    protected $allowedCountryModel;    
    
    protected $_countryFactory;
    
    
    
     public function __construct(               
        \Magento\Directory\Model\AllowedCountries $allowedCountryModel,
        \Magento\Directory\Model\CountryFactory $countryFactory
    
    ) {
        $this->allowedCountryModel = $allowedCountryModel;
        $this->_countryFactory = $countryFactory;
        
    }

    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    // public function getAllOptions()
    // {
    //     //if ($this->_options === null) {
			   
    //            foreach($this->allowedCountryModel->getAllowedCountries() as $countryCode){						       
	// 		       $this->_options[] =  ['label' => $this->getCountryname($countryCode), 'value' => $countryCode];            
	// 	        }
    //     //}
    //     return $this->_options;
    // }

    public function getAllOptions()
{
    if ($this->_options === null) {
        $this->_options = [];
        foreach ($this->allowedCountryModel->getAllowedCountries() as $countryCode) {						       
            $this->_options[] = [
                'label' => $this->getCountryname($countryCode),
                'value' => $countryCode
            ];            
        }
    }
    return $this->_options;
}

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
		
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    
    
    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
		return $this->getAllOptions();
    }
    
    public function getAllowedCountries(){
		//return $this->allowedCountryModel->getAllowedCountries();
		$allowedCountryarr =  array();
		foreach($this->allowedCountryModel->getAllowedCountries() as $countryCode){
			
			$allowedCountryarr[$countryCode] = $this->getCountryname($countryCode);
		}
		return $allowedCountryarr;
	}
	
	 public function getCountryname($countryCode){    
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
    

    
}
