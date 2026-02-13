<?php
namespace Vendor\CustomConfig\Controller\Vendor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Vendor\CustomConfig\Model\Vendor;

class Save extends Action
{
    protected $vendorModel;

    public function __construct(
        Context $context,
        Vendor $vendorModel
    ) {
        $this->vendorModel = $vendorModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                // Map form field names to database attribute codes
                $this->vendorModel->setData('b_name', $data['business_name']);
                $this->vendorModel->setData('business_descriptions', $data['about_store']);
                $this->vendorModel->setData('website', $data['business_website']);

                // You can get the vendor ID from the session or an authenticated user
                // $vendorId = $this->_customerSession->getCustomerId();
                // $this->vendorModel->load($vendorId);
                
                // Save the data to the vendor tables
                $this->vendorModel->save();

                $this->messageManager->addSuccess(__('Your vendor profile has been updated.'));
                return $resultRedirect->setPath('vendor/profile/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('vendor/profile/index');
    }
}