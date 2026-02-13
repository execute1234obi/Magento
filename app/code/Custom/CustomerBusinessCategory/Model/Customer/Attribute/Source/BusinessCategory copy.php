<?php
namespace Custom\CustomerBusinessCategory\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;

class BusinessCategory extends AbstractSource
{
    protected $optionCollectionFactory;
    protected $attribute;

    public function __construct(OptionCollectionFactory $optionCollectionFactory)
    {
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $collection = $this->optionCollectionFactory->create()
                ->setAttributeFilter('business_category')
                ->setPositionOrder('asc')
                ->setStoreFilter(0) // 0 = Admin store, use current store if needed
                ->load();

            $options = [];
            $options[] = ['label' => __('Please Select'), 'value' => ''];

            foreach ($collection as $option) {
                $options[] = [
                    'label' => $option->getValue(),
                    'value' => $option->getOptionId(),
                ];
            }

            $this->_options = $options;
        }
        return $this->_options;
    }
}
