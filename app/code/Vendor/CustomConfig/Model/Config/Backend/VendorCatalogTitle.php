<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Backend model for the Catalog Title configuration field.
 * Handles saving and optional validation before saving.
 */
class VendorCatalogTitle extends Value
{
    /**
     * Perform any processing or validation before saving the value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        // Optional: Basic validation - ensure title is not empty
        if ($value === null || trim($value) === '') {
            throw new LocalizedException(__('Catalog Title cannot be empty.'));
        }

        // Optional: Trim whitespace
        $this->setValue(trim($value));

        return parent::beforeSave();
    }
}
