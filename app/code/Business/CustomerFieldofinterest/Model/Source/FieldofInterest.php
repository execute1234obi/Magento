<?php

namespace Business\CustomerFieldofinterest\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class FieldofInterest extends AbstractSource
{
	
	protected $eavConfig;


    public function __construct(\Magento\Eav\Model\Config $eavConfig){
		$this->eavConfig = $eavConfig;
	}
	/**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
		$this->_options =  $this->getBusinessCategories();		
        /*$this->_options = [
                ['label'=>__('Label 1'),'value'=>'1'],
                ['label'=>__('Label 2'),'value'=>'2'],
                ['label'=>__('Label 3'),'value'=>'3'],
                ['label'=>__('Label 4'),'value'=>'4']
            ];*/
         return $this->_options;
     }
     
   public function getBusinessCategories(){
		$attribute = $this->eavConfig->getAttribute('vendor', 'business_category');
        $options = $attribute->getSource()->getAllOptions();
        return $options;
	}
    
}
