<?php

namespace Vnecoms\VendorsMembership\Block\Adminhtml\Product\Edit\Renderer;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Adminhtml tier price item renderer.
 */
class Duration extends Widget implements RendererInterface
{
    /**
     * @var \Vnecoms\VendorsMembership\Model\Source\DurationUnit
     */
    protected $_durationUnit;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/duration.phtml';

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context       $context
     * @param \Vnecoms\Membership\Model\Source\DurationUnit $durationUnit
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Vnecoms\VendorsMembership\Model\Source\DurationUnit $durationUnit,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_durationUnit = $durationUnit;
        $this->_coreRegistry = $registry;
        $this->_localeCurrency = $localeCurrency;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * Sort values.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, [$this, '_sortDuration']);

        return $data;
    }

    /**
     * Sort tier price values callback method.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _sortDuration($a, $b)
    {
        if ($a['sort_order'] != $b['sort_order']) {
            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
        }

        return 0;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Duration'), 'onclick' => 'return durationControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_duration_button');

        $this->setChild('add_button', $button);

        return parent::_prepareLayout();
    }

    /**
     * Get Duration Unit.
     * 
     * @return array
     */
    public function getDurationUnits()
    {
        return $this->_durationUnit->getAllOptions();
    }

    /**
     * Retrieve current product instance.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Render HTML.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        return $this->toHtml();
    }

    /**
     * Set form element instance.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group\AbstractGroup
     */
    public function setElement(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_element = $element;

        return $this;
    }

    /**
     * Retrieve form element instance.
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Retrieve 'add group price item' button HTML.
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    /**
     * Retrieve Group Price entity attribute.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Check group price attribute scope is global.
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getAttribute()->isScopeGlobal();
    }

    /**
     * Show group prices grid website column.
     *
     * @return bool
     */
    public function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->_storeManager->isSingleStoreMode()) {
            return false;
        }

        return true;
    }

    /**
     * Check is allow change website value for combination.
     *
     * @return bool
     */
    public function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->getProduct()->getStoreId()) {
            return false;
        }

        return true;
    }

    /**
     * Prepare group price values.
     *
     * @return array
     */
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }

        $currency = $this->_localeCurrency->getCurrency($this->_directoryHelper->getBaseCurrencyCode());

        foreach ($values as &$value) {
            $value['readonly'] = $this->isShowWebsiteColumn() &&
            !$this->isAllowChangeWebsite();

            $value['price'] = $currency->toCurrency(
                $value['price'],
                ['display' => \Magento\Framework\Currency::NO_SYMBOL]
            );
        }

        return $values;
    }
}
