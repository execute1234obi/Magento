<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Vnecoms\VendorsMessage\Model\Message;
use Vnecoms\VendorsMessage\Model\Message\Attachment;
use Magento\Framework\App\Action\Context;

class Reply extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_messageHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * Reply constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Vnecoms\VendorsMessage\Helper\Data $messageHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_messageHelper = $messageHelper;
        $this->_resultJsonFactory = $jsonFactory;
        $this->_localeDate = $localeDate;
        $this->customerRepository = $customerRepository;
        $this->_urlBuilder = $context->getUrl();
        $this->urlInterface =  $urlInterface;
        parent::__construct($context);
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $message = $this->_objectManager->create(\Vnecoms\VendorsMessage\Model\Message::class);
        $message->load($this->getRequest()->getParam('id'));
        $response = [];
        try {
            if (!$message->getId() || $message->getOwnerId() != $this->_customerSession->getCustomerId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__("The message is not available."));
            }

            $sender = $this->_customerSession->getCustomer();
            $firstMessageDetail = $message->getFirstMessageDetail();
            /*The receiver id is the id that different with the owner id*/
            $receiverId = $sender->getId() != $firstMessageDetail->getReceiverId()
                ? $firstMessageDetail->getReceiverId() : $firstMessageDetail->getSenderId();
            $receiver = $this->_objectManager->create(\Magento\Customer\Model\Customer::class);
            $receiver->load($receiverId);

            $attachments = $request->getParam('attachments');
            if ($attachments) {
                $attachments = explode("||", $attachments);
            }

            $msgDetailData = [
                'sender_id' => $sender->getId(),
                'sender_email' => $sender->getEmail(),
                'sender_name' => $sender->getName(),
                'receiver_id' => $receiver->getId(),
                'receiver_email' => $receiver->getEmail(),
                'receiver_name' => $receiver->getName(),
                'subject' => __("Re: %1", $firstMessageDetail->getSubject()),
                'content' => $this->getRequest()->getParam('message'),
                'is_read' => 0,
                'created_at' => $this->_localeDate->date(),
                'attachments' => $attachments
            ];

            $errors = [];
            $warnings = [];
            $transport = new \Magento\Framework\DataObject(
                [
                    'detail_data' => $msgDetailData,
                    'errors' => $errors,
                    'warnings' => $warnings
                ]
            );
            /*Save the message to sender outbox*/
            $this->_eventManager->dispatch(
                'messsage_prepare_save',
                [
                    'transport' => $transport,
                ]
            );
            $errors = $transport->getErrors();
            $warnings = $transport->getWarnings();
            if ($errors) {
                throw new \Magento\Framework\Exception\LocalizedException(implode("<br />", $errors));
            }

            $result = [];

            $messageDetail = $this->_objectManager->create(\Vnecoms\VendorsMessage\Model\Message\Detail::class);

            $relationMessage = $message->getRelationMessage();

            /*Save the detail message to sender outbox*/
            $messageDetail->setData($msgDetailData)->setData('is_read', 1)->setMessageId($message->getId())->save();

            if ($warnings) {
                $result["msg"] = implode("<br />", $warnings);
                $warningData = [
                    'message_id' => $message->getId(),
                    'detail_message_id' => $messageDetail->getId()
                ];
                $warning = $this->_objectManager->create(\Vnecoms\VendorsMessage\Model\Warning::class);
                $warning->setData($warningData)->save();
            }

            /*save the detail message to receiver inbox*/
            $messageDetail->setData($msgDetailData)->setMessageId($relationMessage->getId())->save();

            $messageData = $messageDetail->getData();

            $messageData["owner_id"] = $message->getOwnerId();

            $messageData['createdAtDate'] = $this->_localeDate->formatDateTime(
                $messageDetail->getCreatedAt(),
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );
            $messageData['createdAtTime'] = $this->_localeDate->formatDateTime(
                $messageDetail->getCreatedAt(),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::SHORT
            );

            /*Send notification email to receiver*/
            $this->_messageHelper->sendNewReviewNotificationToCustomer($messageDetail);

            /*No matter what message type is just set the is_in_outbox to true*/
            $message->setIsOutbox(1)
                ->setIsDeleted(0)
                ->save();

            /*No matter what message type is just set the is_in_inbox to true*/
            $relationMessage->setIsInbox(1)
                ->setIsDeleted(0)
                ->setStatus(Message::STATUS_UNDREAD)
                ->save();

            if ($attachments) {
                $attachments = [];
                foreach ($messageDetail->getAttachmentCollection() as $attachment) {
                    $attachments[] = [
                        'id' => $attachment->getId(),
                        'name' => $this->getAttachmentName($attachment),
                        'url' => $this->getAttachmentUrl($attachment),
                        'is_image' => $this->isMediaTypeImage($attachment),
                        'icon' => $this->getAttachmentMediaTypeClass($attachment),
                        'file' => $attachment->getFileName(),
                        'download_url' => $this->urlInterface->getUrl(
                            'message/attachment/download',
                            ['file' => base64_encode($attachment->getFileName())]
                        ),
                    ];
                }
                $messageData['attachments'] = $attachments;
                $messageData['attachments_count'] = count($attachments);
            }

            $customer = $this->getCustomer($messageData['sender_id']);
            $avatarFile = $customer->getCustomAttribute('profile_picture');
            $file = $avatarFile ? $avatarFile->getValue() : false;
            $messageData['avatar_url'] = $this->_messageHelper->getAvatarOfCustomer($file);

            $this->_coreRegistry->register('current_message', $message);
            $this->_coreRegistry->register('message', $message);

            $response['error'] = false;
            $response['data'] = $messageData;

        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'msg' => $e->getMessage(),
            ];
        }

        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
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
                return 'message-icon-image';
            default: return 'message-icon-file-empty';
        }
    }
}
