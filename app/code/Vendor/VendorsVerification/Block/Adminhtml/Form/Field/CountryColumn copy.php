<?php
declare(strict_types=1);

namespace Vendor\VendorsVerification\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\View\Element\Context;

class CountryColumn extends Select
{
    /**
     * @var Country
     */
    protected $countrySource;

    /**
     * @param Context $context
     * @param Country $countrySource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Country $countrySource,
        array $data = []
    ) {
        $this->countrySource = $countrySource;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    // public function _toHtml(): string
    // {
    //     if (!$this->getOptions()) {
    //         $this->setOptions($this->getSourceOptions());
    //     }
    //     return parent::_toHtml();
    // }
    public function _toHtml(): string
{
    if (!$this->getOptions()) {
        $options = $this->getSourceOptions();

        array_unshift($options, [
            'value' => '',
            'label' => __('-- Please Select --')
        ]);

        $this->setOptions($options);
    }
    return parent::_toHtml();
}

    /**
     * Fetch countries using Magento Core Source Model
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        // Ye Magento ki default allowed countries fetch karega
        return $this->countrySource->toOptionArray();
    }
}