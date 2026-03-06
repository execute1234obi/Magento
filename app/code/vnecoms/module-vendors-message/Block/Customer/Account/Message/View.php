<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account\Message;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Vnecoms\VendorsMessage\Model\Message\Attachment;

/**
 * Shopping cart item render block for configurable products.
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $helper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Vnecoms\VendorsMessage\Helper\Data $helper
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\VendorsMessage\Helper\Data $helper,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
    }

    public function getJsLayout()
    {
        $this->jsLayout['components']['customer-messages']['component'] = 'Vnecoms_VendorsMessage/js/messages';
        $this->jsLayout['components']['customer-messages']['template'] = 'Vnecoms_VendorsMessage/messages';
        $this->jsLayout['components']['customer-messages']['messages'] = $this->getMessages();
        $this->jsLayout['components']['customer-messages']['message_id'] = $this->getMessage()->getId();
        $this->jsLayout['components']['customer-messages']['message_subject'] = $this->getMessage()->getFirstMessageDetail()->getSubject();
        $this->jsLayout['components']['customer-messages']['addMessageUrl'] = $this->getSendUrl();
        $this->jsLayout['components']['customer-messages']['content_css'] = $this->getContentCss();
        $this->jsLayout['components']['customer-messages']['children'] = [
            [
                'component' => 'Vnecoms_VendorsMessage/js/uploader',
                'template' => 'Vnecoms_VendorsMessage/uploader/uploader',
                'previewTmpl' => 'Vnecoms_VendorsMessage/uploader/preview',
                'displayArea' => 'uploader',
                'maxFileSize' => $this->helper->getMaxSize(),
                'uploaderConfig' => [
                    'url' => $this->getUrl('customer/attachment/upload'),
                    'acceptFileTypes' => explode(',',$this->helper->getAllowedExtensions()),
                    'maxFileNumber' => $this->helper->getMaxNumber() //default 5 files
                ]
            ],
        ];
        return \Laminas\Json\Json::encode($this->jsLayout);
    }

    /**
     * Get attachment file name
     *
     * @param Attachment $attachment
     * @return string
     */
    public function getAttachmentName(Attachment $attachment){
        $name = $attachment->getFileName();
        $name = explode('/', $name);
        $name = end($name);
        return $name;
    }

    /**
     * Get attachment URL
     *
     * @param Attachment $attachment
     * @return string
     */
    public function getAttachmentUrl(Attachment $attachment){
        return $this->_urlBuilder->getBaseUrl([
                '_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ]).'ves_vendorsmessage/'.trim($attachment->getFileName(), '/');
    }

    /**
     * @param Attachment $attachment
     * @return bool
     */
    public function isMediaTypeImage(Attachment $attachment)
    {
        $file = $attachment->getFileName();
        $extension = pathinfo(strtolower($file), PATHINFO_EXTENSION);
        return in_array($extension,['png','jpg','jpeg','gif']);
    }

    /**
     * Get attachment file extension
     *
     * @param Attachment $attachment
     * @return string
     */
    public function getAttachmentMediaTypeClass(Attachment $attachment) {
        $ext = pathinfo(strtolower($attachment->getFileName()), PATHINFO_EXTENSION);
        switch($ext){
            case 'rar':
            case 'tgz':
            case 'bz':
            case 'zip':
                return 'message-icon-file-zip';
            case 'pdf':
                return 'message-icon-file-pdf';
            case 'doc':
            case 'docx':
                return 'message-icon-file-word';
            case 'xls':
            case 'xlsx':
                return 'message-icon-file-excel';
            case 'png':
            case 'jpeg':
            case 'jpg':
            case 'gif':
                return 'message-icon-file-image';
            default: return 'message-icon-file-empty';
        }
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMessages()
    {
        $result = [];
        $messageCollection = $this->getMessage()->getMessageDetailCollection();
        foreach ($messageCollection as $message) {
            /** @var \Vnecoms\VendorsMessage\Model\Message\Detail $message */
            $messageData = $message->getData();
            $messageData['owner_id'] = $this->getMessage()->getOwnerId();
            $messageData['createdAtDate'] =
                $this->formatDate($message->getCreatedAt(), \IntlDateFormatter::MEDIUM, true);
            $messageData['createdAtTime'] = $this->formatTime($message->getCreatedAt());
            $attachments = [];

            foreach ($message->getAttachmentCollection() as $attachment) {
                $attachments[] = [
                    'id' => $attachment->getId(),
                    'name' => $this->getAttachmentName($attachment),
                    'url' => $this->getAttachmentUrl($attachment),
                    'is_image' => $this->isMediaTypeImage($attachment),
                    'icon' => $this->getAttachmentMediaTypeClass($attachment),
                    'file' => $attachment->getFileName(),
                    'download_url' => $this->getUrl(
                        'customer/attachment/download',
                        ['file' => base64_encode($attachment->getFileName())]
                    ),
                ];
            }
            $messageData['attachments'] = $attachments;
            $messageData['attachments_count'] = count($attachments);
            $customer = $this->getCustomerById($messageData['sender_id']);
            $avatarFile = $customer->getCustomAttribute('profile_picture');
            $file = $avatarFile ? $avatarFile->getValue() : false;
            $messageData['avatar_url'] = $this->helper->getAvatarOfCustomer($file);
            $result[] = $messageData;
        }

        return $result;
    }

    /**
     * Get Current Message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage()
    {
        return $this->_coreRegistry->registry('message');
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        $box = $this->getRequest()->getParam('box');
        if($box == 'outbox'){
            $box = 'sent';
        }
        if($box == "inbox") {
            $box = 'index';
        }
        return $this->getUrl("customer/message/".$box);
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("customer/message/delete", ['id' => $this->getMessage()->getId()]);
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl("customer/message/reply", ['id' => $this->getMessage()->getId()]);
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
