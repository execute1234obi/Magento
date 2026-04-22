<?php
namespace Vendor\CustomConfig\Plugin;

class GlobalAccessPlugin
{
    protected $vendorSession;
    protected $resultRedirectFactory;
    protected $messageManager;

    public function __construct(
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->vendorSession = $vendorSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    public function beforeExecute($subject)
    {
        $vendor = $this->vendorSession->getVendor();

        if ($vendor && $vendor->getId()) {
            $expiryDate = $vendor->getExpiryDate();
            
            // Debugging ke liye (Check karein ki date sahi mil rahi hai ya nahi)
            // if (!$expiryDate) { return null; } 

            if ($expiryDate && strtotime($expiryDate) < time()) {
                // Purane messages clear karein taaki memory crash na ho
                $this->messageManager->getMessages(true);
                
                $this->messageManager->addErrorMessage(
                    __('Your membership has expired. Please renew to access this page.')
                );

                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('vendors/dashboard/index');
            }
        }
        return null;
    }
}