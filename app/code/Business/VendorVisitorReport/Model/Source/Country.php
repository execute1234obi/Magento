<?php

namespace Business\VendorVisitorReport\Model\Source;

use Magento\Framework\DB\Ddl\Table;
use Business\VisitorcountryReport\Model\ResourceModel\Country\CollectionFactory as countriesCollectionFactory;

//class Country extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
class Country implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var countriesCollectionFactory
     */
    protected $_countriesCollectionFactory;

    
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;
    
    
     public function __construct(        
        countriesCollectionFactory $countriesCollectionFactory
    ) {
        $this->_countriesCollectionFactory = $countriesCollectionFactory;        
    }

    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
			$countryCollection = $this->_countriesCollectionFactory->create();			
			if(count($countryCollection)){
			   foreach($countryCollection as $country){
				   $this->_options[] =['label' => $country->getCountryName(), 'value' => $country->getId()];
			   }
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
    

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
   /* public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Product approval attribute',
            ],
        ];
    }*/
}
