<?php
namespace Vendor\CustomConfig\Plugin;

use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Vnecoms\Vendors\Model\Vendor;

class ConfigAccessPlugin
{
    protected $vendorSession;
    protected $resultRedirectFactory;
    protected $messageManager;

    public function __construct(
        VendorSession $vendorSession,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->vendorSession = $vendorSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(
        \Vnecoms\VendorsConfig\Controller\Vendors\Index\Edit $subject,
        \Closure $proceed
    ) {
        $vendor = $this->vendorSession->getVendor();

        if ($vendor && $vendor->getId()) {

            $status     = $vendor->getStatus();
            $expiryDate = $vendor->getExpiryDate();

            $isExpired  = $expiryDate && strtotime($expiryDate) < time();
            $isPending  = ($status == Vendor::STATUS_PENDING);
            $isDisabled = ($status == Vendor::STATUS_DISABLED);

            // ❌ Block all non-approved vendors
            if ($isExpired || $isPending || $isDisabled) {

                // 🔥 Clear old messages (important)
                $this->messageManager->getMessages(true);

                // 🔥 Dynamic message
                if ($isPending) {
                    $msg = __('Your seller account status is Pending, You can not access to this functionality.');
                } elseif ($isDisabled) {
                    $msg = __('Your seller account is Disabled. Please contact admin.');
                } else {
                    $msg = __('Your seller account status is Expired, You can not access to this functionality.');
                }

                $this->messageManager->addErrorMessage($msg);

                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('dashboard/index');
            }
        }

        return $proceed();
    }
}