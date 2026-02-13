<?php
namespace Vendor\VendorsVerification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\Source\Status;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

// Interface add karein: CsrfAwareActionInterface
class RejectEntire extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    protected $vendorVerificationFactory;

    public function __construct(
        Context $context,
        VendorVerificationFactory $vendorVerificationFactory
    ) {
        parent::__construct($context);
        $this->vendorVerificationFactory = $vendorVerificationFactory;
    }

    /**
     * CSRF Validation bypass karne ke liye
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool {
        return true;
    }

    public function execute()
    {
        $verificationId = (int)$this->getRequest()->getParam('id');
        if (!$verificationId) {
            $this->messageManager->addErrorMessage(__('Invalid ID.'));
            return $this->_redirect('*/*/');
        }

        try {
            $verification = $this->vendorVerificationFactory->create()->load($verificationId);
            if ($verification->getId()) {
                // RECORD DELETE NAHI KARNA, SIRF STATUS CHANGE
                $verification->setStatus(Status::VENDOR_VERIFICATION_STATUS_ENTIRE_REJECTED);
                $verification->setIsVerified(0);
                $verification->save();

                $this->messageManager->addSuccessMessage(__('Vendor verification has been rejected. Process restarted for vendor.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: ') . $e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }
}