<?php

namespace Vendor\VendorsVerification\Controller\Vendors\Verification;

use Vnecoms\Vendors\App\Action\Context;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Vendor\VendorsVerification\Model\VendorVerificationFactory;
use Vendor\VendorsVerification\Model\VerificationInfoFactory;
use Vendor\VendorsVerification\Model\Source\InfoGroup;
use Vendor\VendorsVerification\Model\Source\Status;
use Vendor\VendorsVerification\Helper\Data as VerificationHelper;
use Magento\Framework\App\ResourceConnection;
use Vendor\VendorsVerification\Service\VendorVerificationService;

class UpdateVerification extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    protected $_aclResource = 'Vnecoms_Vendors::vendorverification_action_save';

    protected $resourceConnection;
    protected $vendorSession;
    protected $vendorFactory;
    protected $verificationFactory;
    protected $verificationInfoFactory;
    protected $helper;
    protected $verificationService;
    public function __construct(
        Context $context,
        VendorSession $vendorSession,
        VendorVerificationFactory $verificationFactory,
        VerificationInfoFactory $verificationInfoFactory,
        VerificationHelper $helper,
        ResourceConnection $resourceConnection,
         VendorVerificationService $verificationService,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
    ) {
        parent::__construct($context);
        $this->vendorSession = $vendorSession;
        $this->verificationFactory = $verificationFactory;
        $this->verificationInfoFactory = $verificationInfoFactory;
        $this->helper = $helper;
        $this->resourceConnection = $resourceConnection;
        $this->vendorFactory = $vendorFactory;
         $this->verificationService = $verificationService;
    }
        public function execute()
{
    $data = $this->getRequest()->getParams();

    try {
        $vendorSess = $this->vendorSession->getVendor();
        if (!$vendorSess || !$vendorSess->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Session expired. Please login again.')
            );
        }

        $vendor = $this->vendorFactory->create()->load($vendorSess->getId());

        $this->verificationService->updateVerification(
            $vendor,
            (int)$data['id'],
            (int)$data['dtl_id'],
            (int)$data['typ_id'],
            $this->getGroupData((int)$data['typ_id'], $data)
        );

        $this->messageManager->addSuccess(
            __('Verification data updated successfully.')
        );

    } catch (\Magento\Framework\Exception\LocalizedException $e) {
        $this->messageManager->addError($e->getMessage());
    } catch (\Exception $e) {
        $this->messageManager->addError(__('Something went wrong.'));
    }

    return $this->_redirect('vendorverification/verification/index');
}

    }

