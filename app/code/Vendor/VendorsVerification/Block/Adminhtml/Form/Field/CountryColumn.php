<?php
declare(strict_types=1);

namespace Vendor\VendorsVerification\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\View\Element\Context;

class CountryColumn extends Select
{
    protected $countrySource;

    public function __construct(
        Context $context,
        Country $countrySource,
        array $data = []
    ) {
        $this->countrySource = $countrySource;
        parent::__construct($context, $data);
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $options = $this->countrySource->toOptionArray();

            // VERY IMPORTANT for JS template
            array_unshift($options, [
                'value' => '',
                'label' => __('-- Select Country --')
            ]);

            $this->setOptions($options);
        }

        // VERY IMPORTANT
        $this->setExtraParams('style="width:150px"');

        return parent::_toHtml();
    }
}
