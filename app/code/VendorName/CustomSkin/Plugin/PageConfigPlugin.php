<?php
namespace VendorName\CustomSkin\Plugin;

use Magento\Framework\View\Page\Config;

class PageConfigPlugin
{
    protected $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function beforeAddPageAsset(Config $subject, $asset, $contentType)
    {
        // वर्तमान URL पाथ प्राप्त करें
        $pathInfo = $this->request->getOriginalPathInfo();

        // जाँच करें कि क्या यह वेंडर डैशबोर्ड URL है और CSS कंटेंट है
        if ($contentType === 'css' && strpos($pathInfo, '/vendors/') !== false) {

            // VNECOM डैशबोर्ड के लिए अपनी CSS फ़ाइल जबरदस्ती जोड़ें
            // सुनिश्चित करें कि यह आपके module.xml में दिए गए VendorName और Module Name से मेल खाता हो।
            $subject->addPageAsset('VendorName_CustomSkin::css/custom_vnecoms_styles.css', ['content_type' => 'css']);
        }

        return [$asset, $contentType];
    }
}