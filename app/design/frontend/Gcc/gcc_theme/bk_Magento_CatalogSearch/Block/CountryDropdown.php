<?php
namespace Vendor\Module\Block;

use Magento\Framework\View\Element\Template;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class CountryDropdown extends Template
{
    protected $countryCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCountryOptions()
    {
        return $this->countryCollectionFactory->create()
            ->loadByStore()
            ->toOptionArray();
    }
}
