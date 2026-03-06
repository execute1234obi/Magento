<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Adminhtml\Messages;

use Vnecoms\VendorsMessage\Model\Message\Attachment;

/**
 * Vendor Notifications block
 */
class View extends \Magento\Backend\Block\Template
{

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Vnecoms\VendorsMessage\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\VendorsMessage\Helper\Data $helper,
        array $data = []
    ) {
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    public function getJsLayout()
    {
        $this->jsLayout['components']['admin-messages']['component'] = 'Vnecoms_VendorsMessage/js/messages';
        $this->jsLayout['components']['admin-messages']['template'] = 'Vnecoms_VendorsMessage/messages';
        $this->jsLayout['components']['admin-messages']['messages'] = $this->getMessages();
        $this->jsLayout['components']['admin-messages']['message_id'] = $this->getMessage()->getId();
        $this->jsLayout['components']['admin-messages']['message_subject'] = $this->getMessage()->getFirstMessageDetail()->getSubject();
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
     * Retrive all messages and attachments
     *
     * @param void
     * @return string|mixed
     */
    public function getMessages()
    {
        $result = [];
        $messageCollection = $this->getMessage()->getMessageDetailCollection();
        foreach($messageCollection as $message){
            /** @var \Vnecoms\VendorsMessage\Model\Message\Detail $message */

            $messageData = $message->getData();
            $messageData['owner_id'] = $this->getMessage()->getOwnerId();
            $messageData['createdAtDate'] = $this->formatDate($message->getCreatedAt(), \IntlDateFormatter::MEDIUM, true);
            $messageData['createdAtTime'] = $this->formatTime($message->getCreatedAt());
            $attachments = [];

            foreach($message->getAttachmentCollection() as $attachment){
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
            $messageData['attachments_count'] = sizeof($attachments);
            $result[] = $messageData;
        }

        return $result;
    }


    /**
     * Get message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage(){

        return $this->_coreRegistry->registry('message');
    }

    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl(){
        return $this->getUrl("vendors/message_all");
    }


}
