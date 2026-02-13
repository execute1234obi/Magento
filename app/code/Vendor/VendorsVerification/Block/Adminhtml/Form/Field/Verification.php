<?php

namespace Vendor\VendorsVerification\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Verification
 */
class Verification extends AbstractFieldArray
{
    /**
     * @var CountryColumn
     */
    private $countryRenderer;

    /**
     * Prepare rendering the columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('countrycode', [
            'label' => __('Country'),
            'renderer' => $this->getCountryRenderer()
        ]);
        // 'class' required-number ensures only numbers are entered
        $this->addColumn('fees', [
            'label' => __('Fees'), 
            'class' => 'required-entry validate-number'
        ]);
        
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $countryCode = $row->getCountrycode(); // Match column id: countrycode

        if ($countryCode !== null) {
            $options['option_' . $this->getCountryRenderer()->calcOptionHash($countryCode)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get the renderer for the country column
     *
     * @return CountryColumn
     * @throws LocalizedException
     */
    private function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                \Vendor\VendorsVerification\Block\Adminhtml\Form\Field\CountryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }
    
}