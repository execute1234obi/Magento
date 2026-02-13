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

    protected function _getElementHtml(AbstractElement $element)
{
    $value = $element->getValue();
    if (is_array($value) && isset($value['value'])) {
        $value = $value['value'];
    }

    $inputId = $element->getHtmlId();
    $inputName = $element->getName();
    $label = $element->getLabel();

    $html = '<div id="row_' . $inputId . '" class="form-group field-' . $inputId . '">';
    $html .= '<label class="col-sm-4 control-label" for="' . $inputId . '">' . $label . '</label>';
    $html .= '<div class="col-sm-5 control value with-tooltip">';

    // ✅ File input field first (as per Magento layout)
    $html .= '<input id="' . $inputId . '" name="' . $inputName . '" ' .
             'class="input-file" type="file" style="margin-bottom:10px;position:relative;z-index:2;" />';

    // ✅ Now show download link BELOW the file input
    if (!empty($value)) {
        $mediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
        $fileUrl = $mediaUrl . ltrim($value, '/');
        $fileName = basename($value);

         // Wrapper styled similar to Magento’s default spacing
//     $html .= '<div class="pdf-download-wrapper" 
//     style="
//         margin-top: 12px;
//         display: block;
//         clear: both;
//         position: relative;
//         z-index: 9999;
//         overflow: visible !important;
//     ">
//     <a href="' . $fileUrl . '" 
//        target="_blank"
//        style="
//            font-weight: 600;
//            color: #007bff;
//            text-decoration: none;
//            display: inline-block;
//            background: #fff;
//            padding: 3px 5px;
//            border-radius: 4px;
//            position: relative;
//            z-index: 9999;
//                background: red;
//     height: 20px !important;
//        ">
//        📄 Download ' . htmlspecialchars($fileName) . '
//     </a>
// </div>';
$html .= '<div class="pdf-download-wrapper" 
    style="margin-top: 10px; display: block; clear: both;">
    <span>
        <a href="' . $fileUrl . '" target="_blank">
            <img alt="Download file" title="Download file" 
                 src="' . $this->_assetRepo->getUrl('Vnecoms_vendor::images/fam_bullet_disk.gif') . '" 
                 class="v-middle" 
                 style="margin-right:5px;"/>
        </a>
        <a href="' . $fileUrl . '" target="_blank" 
           style="font-weight:600;color:#007bff;text-decoration:none;">
           Download
        </a>
    </span>
</div>';

    }

    // ✅ Optional delete checkbox
    if (!empty($value)) {
        $html .= '<span class="delete-image" style="display:block;margin-top:6px;">';
        $html .= '<input type="checkbox" name="' . $inputName . '[delete]" value="1" class="checkbox" id="' . $inputId . '_delete">';
        $html .= '<label for="' . $inputId . '_delete"> Delete File</label>';
        $html .= '<input type="hidden" name="' . $inputName . '[value]" value="' . $value . '">';
        $html .= '</span>';
    }

    // ✅ Tooltip and note
    $html .= '<div class="tooltip"><span class="help"><span></span></span><div class="tooltip-content"></div></div>';
    $html .= '<p class="note"><span>Allowed file type: PDF only</span></p>';

    $html .= '</div></div>';

    return $html;
}

}
