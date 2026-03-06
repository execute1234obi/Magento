<?php

namespace Vnecoms\VendorsMembership\Model\Source;

class Group extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Vnecoms\Vendors\Model\ResourceModel\Group\CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_converter;


    
    public function __construct(
        \Vnecoms\Vendors\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Framework\Convert\DataObject $converter
    ) {
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_converter = $converter;
    }


    /**
     * (non-PHPdoc).
     *
     * @see \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface::getAllOptions()
     */
    public function getAllOptions($blankLine = false)
    {
        if (!$this->_options) {
            $this->_options = [];
            $groups = $this->_groupCollectionFactory->create();
            foreach ($groups as $group) {
                $this->_options[] = [
                    'value' => $group->getId(),
                    'label' => $group->getVendorGroupCode(),
                ];
            }
            if ($blankLine) {
                array_unshift($this->_options, ['value' => '', 'label' => __('-- Please Select --')]);
            }
        }

        return $this->_options;
    }

    /**
     * Retrieve option array.
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->toOptionArray() as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
