<?php
namespace Vnecoms\VendorsMessage\Block;

class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;


    /**
     * @var \Vnecoms\Vendors\Helper\Data
     */
    protected $_vendorHelper;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_messageHelper;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManage;


    /**
     * Message constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Vnecoms\Vendors\Helper\Data $vendorHelper
     * @param \Vnecoms\VendorsMessage\Helper\Data $messageHelper
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Framework\Module\Manager $moduleManage
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Module\Manager $moduleManage,
        array $data = []
    ) {
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_vendorFactory = $vendorFactory;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->_vendorHelper = $vendorHelper;
        $this->_messageHelper = $messageHelper;
        $this->_objectManager = $objectmanager;
        $this->_moduleManage    = $moduleManage;
    }

    public function getJsLayout()
    {
        $this->jsLayout['components']['message-uploader']['component'] = 'Vnecoms_VendorsMessage/js/uploader';
        $this->jsLayout['components']['message-uploader']['template'] = 'Vnecoms_VendorsMessage/uploader/uploader';
        $this->jsLayout['components']['message-uploader']['previewTmpl'] = 'Vnecoms_VendorsMessage/uploader/preview';
        $this->jsLayout['components']['message-uploader']['displayArea'] = 'uploader';
        $this->jsLayout['components']['message-uploader']['maxFileSize'] = $this->_messageHelper->getMaxSize();
        $this->jsLayout['components']['message-uploader']['content_css'] = $this->getContentCss();
        $this->jsLayout['components']['message-uploader']['uploaderConfig'] =  [
            'url' => $this->getUrl('customer/attachment/upload', ['_secure' => true]),
            'acceptFileTypes' => explode(',',$this->_messageHelper->getAllowedExtensions()),
            'maxFileNumber' => $this->_messageHelper->getMaxNumber() //default 5 files
        ];
        return \Laminas\Json\Json::encode($this->jsLayout);
    }

    /**
     * Get vendor object
     *
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor()
    {
        $vendor = $this->_coreRegistry->registry('vendor');
        if (!$vendor && $product = $this->_coreRegistry->registry('product')) {
            if ($vendorId = $product->getVendorId()) {
                $vendor = $this->_vendorFactory->create()->load($vendorId);
            }
        }
         return $vendor;
    }

    /**
     * Is logged in customer
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Get send message URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('customer/message/send', ['vendor_id' => $this->getVendor()->getId()]);
    }

    /**
     * Can send message
     *
     * @return boolean
     */
    public function canSendMessage()
    {
        if ($this->getVendor()->getCustomer()) {
            $sellerCustomerId = $this->getVendor()->getCustomer()->getId();
            return $this->_customerSession->getCustomerId() != $sellerCustomerId;
        }
        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        if (!$this->getVendor() || !$this->_vendorHelper->moduleEnabled() ) {
            return '';
        }

        if ($this->_moduleManage->isEnabled("Vnecoms_VendorsGroup")) {
            $groupHelper = $this->_objectManager->create("\Vnecoms\VendorsGroup\Helper\Data");
            $permission =  $groupHelper->getConfig("message/can_use_message", $this->getVendor()->getGroupId());
            if (!$permission) return '';
        }


        return parent::_toHtml();
    }

    /**
     * get custom css for wysiwyg tiny mce
     */
    public function getContentCss()
    {
        $css =  $this->_assetRepo->getUrl(
            'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
        );
        return $css;
    }
}
