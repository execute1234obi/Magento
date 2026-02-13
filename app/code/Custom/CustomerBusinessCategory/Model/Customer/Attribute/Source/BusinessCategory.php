<?php
namespace Custom\CustomerBusinessCategory\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;

class BusinessCategory extends AbstractSource
{
    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * Cache for options, avoids multiple DB calls
     *
     * @var array|null
     */
    protected $_options = null;

    /**
     * Constructor
     *
     * @param OptionCollectionFactory $optionCollectionFactory
     */
    public function __construct(OptionCollectionFactory $optionCollectionFactory)
    {
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Retrieve option values array
     *
     * Returns an array like: [['label' => ..., 'value' => ...], ...]
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $collection = $this->optionCollectionFactory->create()
                ->setAttributeFilter('business_category11')
                ->setPositionOrder('asc')
                ->setStoreFilter(0) // Use Admin store (0) or adapt to current store
                ->load();

            $options = [];
            // Default blank option
            $options[] = ['label' => __('Please select'), 'value' => ''];

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
