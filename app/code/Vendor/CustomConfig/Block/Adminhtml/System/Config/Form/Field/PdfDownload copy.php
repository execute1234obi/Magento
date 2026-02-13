<?php
namespace Vendor\CustomConfig\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;

class PdfDownload extends Field
{
    protected $_urlBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Render Choose File + Download PDF link
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $value = $element->getValue();
        $html = '';

        // Upload input field
        $html .= '<input type="file" name="' . $element->getName() . '" />';
        $html .= '<p style="color:#666;font-size:12px;margin:4px 0;">Allowed file type: PDF only</p>';

        // Delete checkbox (optional)
        //$html .= '<div><label><input type="checkbox" name="' . $element->getName() . '[delete]" value="1" /> Delete File</label></div>';

        // Download link (if PDF exists)
        if ($value) {
            $mediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
            $fileUrl = $mediaUrl . ltrim($value, '/');
            $fileName = basename($value);

            $html .= '<div style="margin-top:6px;">';
            $html .= '<a href="' . $fileUrl . '" target="_blank" ';
            $html .= 'style="text-decoration:none;font-weight:600;color:#007bff;">';
            $html .= '📄 Download ' . htmlspecialchars($fileName) . '</a>';
            $html .= '</div>';
        }

        return $html;
    }
}
