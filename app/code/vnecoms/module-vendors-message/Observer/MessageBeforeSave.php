<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * AdminNotification observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MessageBeforeSave implements ObserverInterface
{
    /**
     * @var \Vnecoms\VendorsMessage\Model\BlockFactory
     */
    protected $_block;

    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Vnecoms\VendorsMessage\Model\BlockFactory $block,
        \Vnecoms\VendorsMessage\Helper\Data $helper,
        array $data = []
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_block = $block;
        $this->_helper = $helper;
    }

    /**
     * Add the notification if there are any vendor awaiting for approval.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $detailData      = $transport->getDetailData();
        $errors      = $transport->getErrors();
        $warnings = $transport->getWarnings();
        $senderId = $detailData["sender_id"];
        $sender = $this->_block->create()->load($senderId,"owner_id");

        if($sender->getId()){
            $errors[] = __("It looks like your account has been blocked,  please contact your site administrator for details.");
            $transport->setErrors($errors);
            return;
        }

        $receiverId = $detailData["receiver_id"];
        $receiver = $this->_block->create()->load($receiverId,"owner_id");
        if($receiver->getId()){
            $errors[] = __("Sorry, you are not be able to send the message to a blocked account.");
            $transport->setErrors($errors);
            return;
        }

        $isPatternError = $this->_helper->processPatternError($detailData["content"]);

        if($isPatternError["flag"] == true){
            $errors[] = $isPatternError["message"];
            $transport->setErrors($errors);
            return;
        }

        $isPatternWarning = $this->_helper->processPatternWarning($detailData["content"]);
        if($isPatternWarning["flag"]  == true){
            $warnings[] = $isPatternWarning["message"];
            $transport->setWarnings($warnings);
            return;
        }
    }
}
