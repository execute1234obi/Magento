<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Warning;

use Magento\Framework\Controller\ResultFactory;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Vnecoms\VendorsMessage\Model\ResourceModel\Warning\CollectionFactory;
use Magento\Backend\App\Action;

class MassBlock extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $warning) {
            try{
                $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
                $messageDetail->load($warning->getDetailMessageId());

                // save sender to block list user
                $senderId = $messageDetail->getSenderId();
                $block = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Block');
                $sender = $block->load($senderId,"owner_id");
                if(!$sender->getId()){
                    $block->setData("owner_id",$senderId)->save();
                }
                $warning->delete();
            }catch(\Exception $e){
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been blocked.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
