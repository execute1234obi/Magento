<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_NEW_MESSAGE_EMAIL_TEMPLATE = 'vendors/vendorsmessage/new_message_notification';
    const XML_PATH_EMAIL_SENDER = 'vendors/vendorsmessage/sender_email_identity';


    /**
     * @var \Vnecoms\Vendors\Helper\Email
     */
    protected $_emailHelper;
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory
     */
    protected $_pattern;

    protected $_storeManager;
	
	/**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Vnecoms\VendorsAvatarProfile\Helper\Data
     */
    protected $avatarHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Vnecoms\Vendors\Helper\Email $emailHelper
     * @param \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory $pattern
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Vnecoms\VendorsAvatarProfile\Helper\Data $avatarHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vnecoms\Vendors\Helper\Email $emailHelper,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory $pattern,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Asset\Repository $assetRepo,
        \Vnecoms\VendorsAvatarProfile\Helper\Data $avatarHelper
    ) {
        parent::__construct($context);
        $this->_emailHelper = $emailHelper;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_pattern = $pattern;
        $this->_storeManager = $storeManager;
		$this->_assetRepo = $assetRepo;
		$this->avatarHelper = $avatarHelper;
    }


    /**
     * Send new message notification email to receiver
     *
     * @param \Vnecoms\VendorsMessage\Model\Message\Detail $messageDetail
     */
    public function sendNewReviewNotificationToCustomer(
        \Vnecoms\VendorsMessage\Model\Message\Detail $messageDetail
    ) {
        $messageURL = $this->_urlBuilder->getUrl('customer/message/view',['id' => $messageDetail->getMessageId()]);
        $this->_emailHelper->sendTransactionEmail(
            self::XML_PATH_NEW_MESSAGE_EMAIL_TEMPLATE,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            self::XML_PATH_EMAIL_SENDER,
            $messageDetail->getReceiverEmail(),
            ['message' => $messageDetail, 'message_url' => $messageURL],
            '',
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @param $message
     * @return array
     */
    public function processPatternWarning($message){
        $patterns = $this->_pattern->create()->addFieldToFilter("action",1)->addFieldToFilter("status",1);
        $warning = ["flag"=>false];
        foreach ($patterns as $pattern){
            // var_dump($message);exit;
            if(preg_match("/".$pattern->getPattern()."/is",$message)){
                $warning["flag"] = true;
                $warning["message"] = $pattern->getMessage();
                break;
            }
        }
        return $warning;
    }

    /**
     * @param $message
     * @return array
     */
    public function processPatternError($message){
        $patterns = $this->_pattern->create()->addFieldToFilter("action",0)->addFieldToFilter("status",1);
        $errors = ["flag"=>false];
        foreach ($patterns as $pattern){
            if(preg_match("/".$pattern->getPattern()."/is",$message)){
                $errors["flag"] = true;
                $errors["message"] = $pattern->getMessage();
                break;
            }
        }
        return $errors;
    }

    /**
     * @param $configId
     * @param null|string|integer $store
     * @return string
     */
    public function getConfig($configId, $store = null)
    {
        if ($store === null) $store = $this->_storeManager->getStore()->getId();

        return $this->scopeConfig->getValue($configId, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $store);
    }

    /**
     * @param string $storeId
     * @return string
     */
    public function getAllowedExtensions($storeId = '')
    {
        return $this->getConfig('vendors/vendorsmessage/allowed_extensions', $storeId);
    }


    public function getMaxSize($storeId = '')
    {
        return $this->getConfig('vendors/vendorsmessage/max_file_size', $storeId);
    }

    public function getMaxNumber($storeId = '')
    {
        return $this->getConfig('vendors/vendorsmessage/max_number_file', $storeId);
    }

    /**
     * @param $file
     * @return bool|string
     */
    public function getAvatarOfCustomer($file)
    {
        return $this->avatarHelper->getAvatarOfCustomer($file);
    }

    /**
     * @param $file
     * @return bool|string
     */
    public function getAvatarOfVendor($file)
    {
        return $this->avatarHelper->getAvatarOfVendor($file);
    }
}
