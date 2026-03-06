<?php

namespace Vnecoms\VendorsMessage\Controller\Vendors\Index;

use Vnecoms\Vendors\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Vnecoms\VendorsMessage\Model\ResourceModel\Message\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassRead extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_VendorsMessage::messages';
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @param Context $context
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
        
        foreach ($collection as $message) {
            $message->markAsRead();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('message');
    }
}
