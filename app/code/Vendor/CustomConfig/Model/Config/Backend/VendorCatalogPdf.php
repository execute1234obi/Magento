<?php
namespace Vendor\CustomConfig\Model\Config\Backend;

use Vnecoms\VendorsConfig\Model\Config\Backend\File as VendorBackendFile;
use Magento\Framework\Exception\LocalizedException;

class VendorCatalogPdf extends VendorBackendFile
{
    protected $_uploadDir = 'ves_vendors/catalog';
    protected $_allowedExtensions = ['pdf'];

    protected function _getUploadDir()
    {
        return $this->_uploadDir;
    }

    /**
     * Override preview URL – always return downloadable link
     */
    public function getPreviewUrl()
    {
        $value = $this->getValue();
        if (!$value) {
            return false;
        }

        // Full URL of PDF
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . $value;
    }

    public function afterSave()
    {
        $value = $this->getValue();

        // Normalize path
        if ($value && strpos($value, $this->_uploadDir) === false) {
            $value = $this->_uploadDir . '/' . ltrim($value, '/');
        }

        // Validate extension
        if ($value && pathinfo($value, PATHINFO_EXTENSION) !== 'pdf') {
            throw new LocalizedException(__("Only PDF files are allowed."));
        }

        $this->setValue($value);

        return parent::afterSave();
    }
}
