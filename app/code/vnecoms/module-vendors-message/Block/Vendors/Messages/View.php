<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Vendors\Messages;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Vnecoms\VendorsMessage\Model\Message\Attachment;

/**
 * Vendor Notifications block
 */
class View extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
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
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Vnecoms\Vendors\Model\UrlInterface $url
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Vnecoms\VendorsMessage\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\VendorsMessage\Helper\Data $helper,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        parent::__construct($context, $url, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    public function getJsLayout()
    {
        $this->jsLayout['components']['vendor-messages']['component'] = 'Vnecoms_VendorsMessage/js/messages';
        $this->jsLayout['components']['vendor-messages']['template'] = 'Vnecoms_VendorsMessage/messages';
        $this->jsLayout['components']['vendor-messages']['messages'] = $this->getMessages();
        $this->jsLayout['components']['vendor-messages']['message_id'] = $this->getMessage()->getId();
        $this->jsLayout['components']['vendor-messages']['loader_image'] =
            $this->getViewFileUrl('images/loader-2.gif');
        $this->jsLayout['components']['vendor-messages']['message_subject'] =
            $this->getMessage()->getFirstMessageDetail()->getSubject();
        $this->jsLayout['components']['vendor-messages']['addMessageUrl'] = $this->getSendUrl();
        $this->jsLayout['components']['vendor-messages']['content_css'] = $this->getContentCss();
        $this->jsLayout['components']['vendor-messages']['children'] = [
            [
                'component' => 'Vnecoms_VendorsMessage/js/uploader',
                'template' => 'Vnecoms_VendorsMessage/uploader/uploader',
                'previewTmpl' => 'Vnecoms_VendorsMessage/uploader/preview',
                'displayArea' => 'uploader',
                'maxFileSize' => $this->helper->getMaxSize(),
                'uploaderConfig' => [
                    'url' => $this->getUrl('message/attachment/upload'),
                    'acceptFileTypes' => explode(',', $this->helper->getAllowedExtensions()),
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
     * Get message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage()
    {
        return $this->_coreRegistry->registry('current_message');
    }

    /**
     * Retrive all messages and attachments
     *
     * @param void
     * @return string|mixed
     */
    public function getMessages()
    {
        $result = [];
        $messageCollection = $this->getMessage()->getMessageDetailCollection();
        foreach ($messageCollection as $message) {
            /** @var \Vnecoms\VendorsMessage\Model\Message\Detail $message */

            $messageData = $message->getData();
            $messageData['createdAtDate'] =
                $this->formatDate($message->getCreatedAt(), \IntlDateFormatter::MEDIUM, true);
            $messageData['createdAtTime'] = $this->formatTime($message->getCreatedAt());
            $messageData['owner_id'] = $this->getMessage()->getOwnerId();
            $attachments = [];

            foreach ($message->getAttachmentCollection() as $attachment) {
                $attachments[] = [
                    'id' => $attachment->getId(),
                    'name' => $this->getAttachmentName($attachment),
                    'icon' => $this->getAttachmentMediaTypeClass($attachment),
                    'url' => $this->getAttachmentUrl($attachment),
                    'is_image' => $this->isMediaTypeImage($attachment),
                    'file' => $attachment->getFileName(),
                    'download_url' => $this->getUrl(
                        'message/attachment/download',
                        ['file' => base64_encode($attachment->getFileName())]
                    ),
                ];
            }
            $messageData['attachments'] = $attachments;
            $customer = $this->getCustomerById($messageData['sender_id']);
            $avatarFile = $customer->getCustomAttribute('profile_picture');
            $file = $avatarFile ? $avatarFile->getValue() : false;
            $messageData['avatar_url'] = $this->helper->getAvatarOfVendor($file);

            $messageData['attachments_count'] = count($attachments);
            $result[] = $messageData;
        }

        return $result;
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerById($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl(){
        return $this->getUrl("message");
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getDeleteUrl(){
        return $this->getUrl("message/view/delete",['id' => $this->getMessage()->getId()]);
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getSendUrl(){
        return $this->getUrl("message/view/reply",['id' => $this->getMessage()->getId()]);
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
