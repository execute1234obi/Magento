<?php
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\Source\Status;
use Magento\Backend\App\Action\Context;

class RejectEntire extends \Magento\Backend\App\Action
{
    protected $vendorVerificationFactory;

    const ADMIN_RESOURCE = 'Vendor_VendorsVerification::verification';

    public function __construct(
        Context $context,
        VendorVerificationFactory $vendorVerificationFactory
    ) {
        parent::__construct($context);
        $this->vendorVerificationFactory = $vendorVerificationFactory;
    }

    public function execute()
    {
        $verificationId = (int)$this->getRequest()->getParam('id');

        if (!$verificationId) {
            $this->messageManager->addErrorMessage(__('Invalid verification record.'));
            return $this->_redirect('*/*/');
        }

        try {
            $verification = $this->vendorVerificationFactory->create()->load($verificationId);

            if (!$verification->getId()) {
                $this->messageManager->addErrorMessage(__('Verification record not found.'));
                return $this->_redirect('*/*/');
            }

            $verification->setStatus(Status::VENDOR_VERIFICATION_STATUS_ENTIRE_REJECTED);
            $verification->setIsVerified(0);
            $verification->save();

            $this->messageManager->addSuccessMessage(
                __('Entire vendor registration rejected. Vendor must re-verify.')
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to reject verification.'));
        }

        return $this->_redirect('*/*/view', ['id' => $verificationId]);
    }
}
