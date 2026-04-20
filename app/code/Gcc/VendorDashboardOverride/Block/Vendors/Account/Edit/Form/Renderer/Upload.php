<?php
namespace Gcc\VendorDashboardOverride\Block\Vendors\Account\Edit\Form\Renderer;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
        $this->setTemplate('Gcc_VendorDashboardOverride::account/form/renderer/upload.phtml');
    }

    /**
     * Media base URL
     *
     * @return string
     */
    public function getMediaBaseUrl()
    {
        return rtrim(
            (string) $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            '/'
        );
    }

    /**
     * Return uploaded file URL.
     *
     * @return string
     */
    public function getUploadedFileUrl()
    {
        $value = trim((string) $this->getElement()->getValue());
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        return $this->getMediaBaseUrl() . '/' . ltrim($value, '/');
    }

    /**
     * Return uploaded file name.
     *
     * @return string
     */
    public function getUploadedFileName()
    {
        $value = trim((string) $this->getElement()->getValue());
        if ($value === '') {
            return '';
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (!$path) {
            $path = $value;
        }

        return basename($path);
    }

    /**
     * Is the current value an image?
     *
     * @return bool
     */
    public function isUploadedImage()
    {
        $value = trim((string) $this->getElement()->getValue());
        if ($value === '') {
            return false;
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (!$path) {
            $path = $value;
        }

        return (bool) preg_match('/\.(jpe?g|png|gif|bmp|webp|svg)$/i', $path);
    }

    /**
     * Allowed file extensions.
     *
     * @return string
     */
    public function getAllowedExtensions()
    {
        return trim((string) $this->getElement()->getDefaultValue());
    }

    /**
     * HTML accept attribute based on allowed extensions.
     *
     * @return string
     */
    public function getAcceptAttribute()
    {
        $default = $this->getAllowedExtensions();
        if ($default === '') {
            return '';
        }

        $extensions = preg_split('/\s*,\s*/', $default) ?: [];
        $extensions = array_filter(array_map(
            static function ($extension) {
                $extension = trim((string) $extension);
                if ($extension === '') {
                    return '';
                }

                return '.' . ltrim($extension, '.');
            },
            $extensions
        ));

        return implode(',', $extensions);
    }
}
