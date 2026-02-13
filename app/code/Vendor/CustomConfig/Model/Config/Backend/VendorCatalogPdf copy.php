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

    public function afterSave()
    {
        $value = $this->getValue();

        if ($value && strpos($value, $this->_uploadDir) === false) {
            $value = $this->_uploadDir . '/' . ltrim($value, '/');
        }

        if ($value && pathinfo($value, PATHINFO_EXTENSION) !== 'pdf') {
            throw new LocalizedException(__("Only PDF files are allowed."));
        }

        $this->setValue($value);

        return parent::afterSave();
    }
    
}
