<?php

namespace Business\CustomerFieldofinterest\Block;

use Magento\Framework\View\Element\Template;

class FieldofInterest extends Template
{      
    
    protected $interestOptions;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Business\CustomerFieldofinterest\Model\Source\FieldofInterest $interestOptions,
        array $data = []
    ) {
        
        $this->interestOptions = $interestOptions;      
        
        parent::__construct($context, $data);
    }

    public function getAllFieldOfInterest(){
		$options = $this->interestOptions->getAllOptions();
		return $options;
	}
}
