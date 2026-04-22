<?php
namespace Vendor\CustomConfig\Plugin;

use Vnecoms\VendorsConfig\Helper\Data as VendorConfigHelper;
use Psr\Log\LoggerInterface;
use Vnecoms\Vendors\Model\Session as VendorSession;
use Magento\Framework\Exception\LocalizedException;
use Vnecoms\Vendors\Model\Vendor;

class VendorConfigSavePlugin
{
    protected $logger;
    protected $vendorSession;

    public function __construct(
        LoggerInterface $logger,
        VendorSession $vendorSession
    ) {
        $this->logger = $logger;
        $this->vendorSession = $vendorSession;
    }

    public function aroundSaveConfig(
        VendorConfigHelper $subject,
        callable $proceed,
        $scope,
        $scopeId,
        $data
    ) {

        // 🔥 STEP 1: VENDOR CHECK
        $vendor = $this->vendorSession->getVendor();

        if ($vendor && $vendor->getId()) {

            $status     = $vendor->getStatus();
            $expiryDate = $vendor->getExpiryDate();

            $isExpired  = $expiryDate && strtotime($expiryDate) < time();
            $isPending  = ($status == Vendor::STATUS_PENDING);
            $isDisabled = ($status == Vendor::STATUS_DISABLED);

            // ❌ BLOCK SAVE FOR ALL NON-APPROVED
            if ($isExpired || $isPending || $isDisabled) {

                // logging (optional but helpful)
                $this->logger->info(
                    "Blocked config save for vendor ID {$vendor->getId()} | Status: {$status}"
                );

                // 🔥 Dynamic Message
                if ($isPending) {
                    $msg = __('Your seller account status is Pending, You can not access to this functionality.');
                } elseif ($isDisabled) {
                    $msg = __('Your seller account is Disabled. Please contact admin.');
                } else {
                    $msg = __('Your seller account status is Expired, You can not access to this functionality.');
                }

                throw new LocalizedException($msg);
            }
        }

        // --- STEP 2: SANITIZATION LOGIC ---
        $sanitizedData = $data;

        $configMap = [
            'general_info/upload_logo',
            'general_info/upload_banner',
            'upload_certificates/certificate_file',
            'upload_catalog/catalog_file'
        ];

        foreach ($configMap as $path) {
            list($section, $fieldCode) = explode('/', $path);

            if (
                isset($sanitizedData[$section]['fields'][$fieldCode]['value']) &&
                is_array($sanitizedData[$section]['fields'][$fieldCode]['value'])
            ) {
                $originalValue = $sanitizedData[$section]['fields'][$fieldCode]['value'];
                $sanitizedValue = '';

                if (isset($originalValue['name']) && !empty($originalValue['name'])) {
                    $sanitizedValue = $originalValue['name'];
                } elseif (isset($originalValue['value']) && !is_array($originalValue['value'])) {
                    $sanitizedValue = $originalValue['value'];
                } elseif (isset($originalValue['delete']) && $originalValue['delete'] == 1) {
                    $sanitizedValue = '';
                }

                $sanitizedData[$section]['fields'][$fieldCode]['value'] = $sanitizedValue;

                $this->logger->info("Sanitized '$path' to '$sanitizedValue'");
            }
        }

        return $proceed($scope, $scopeId, $sanitizedData);
    }
}