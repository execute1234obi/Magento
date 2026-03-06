<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Adminhtml\Message\Pattern;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_VendorsMessage::message_pattern');
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $model = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Pattern');
            if (empty($data['pattern_id'])) {
                $data['pattern_id'] = null;
            }
            $id = $this->getRequest()->getParam('pattern_id');
            if ($id) {
                $model->load($id);
            }
            $model->setData($data);
            $this->_eventManager->dispatch(
                'message_pattern_prepare_save',
                ['pattern' => $model, 'request' => $this->getRequest()]
            );
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['pattern_id' => $model->getId(), '_current' => true]);
            }
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this pattern.'));
                $this->dataPersistor->clear('message_pattern');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['pattern_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->dataPersistor->set('message_pattern', $data);
            return $resultRedirect->setPath('*/*/edit', ['pattern_id' => $this->getRequest()->getParam('pattern_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
