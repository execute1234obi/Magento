<?php

namespace Vendor\CustomConfig\Plugin;

use Vnecoms\VendorsConfig\Helper\Data as VendorConfigHelper;
use Psr\Log\LoggerInterface;

/**
 * Plugin to intercept Vnecoms\VendorsConfig\Helper\Data::saveConfig()
 * and sanitize file upload arrays to prevent "Array to string conversion" errors.
 */
class VendorConfigSavePlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Intercepts the saveConfig method to modify the data array.
     *
     * @param VendorConfigHelper $subject
     * @param callable $proceed
     * @param string $scope
     * @param int $scopeId
     * @param array $data
     * @return mixed
     */
    public function aroundSaveConfig(
    \Vnecoms\VendorsConfig\Helper\Data $subject,
    callable $proceed,
    $scope,
    $scopeId,
    $data
) {
    // Variable initialization to prevent Undefined variable warnings
    $sanitizedData = $data;
    
    // Configuration paths for fields whose *value* is a complex array (file uploads)
    $configMap = [
        'general_info/upload_logo',
        'general_info/upload_banner',
        'upload_certificates/certificate_file',
        'upload_catalog/catalog_file'
    ];

    // FIX: Process the deep array structure
    foreach ($configMap as $path) {
        // Path format: section/field_code (e.g., general_info/upload_logo)
        list($section, $fieldCode) = explode('/', $path);

        // Check for the field at the deep level: $data[$section]['fields'][$fieldCode]['value']
        if (isset($sanitizedData[$section]['fields'][$fieldCode]['value']) && 
            is_array($sanitizedData[$section]['fields'][$fieldCode]['value'])) 
        {
            $originalValue = $sanitizedData[$section]['fields'][$fieldCode]['value'];
            $sanitizedValue = '';

            // --- File Upload Sanitization Logic ---
            if (isset($originalValue['name']) && !empty($originalValue['name'])) {
                // New file upload (use filename)
                $sanitizedValue = $originalValue['name'];
            } elseif (isset($originalValue['value']) && !is_array($originalValue['value'])) {
                // Existing file value (use string path)
                $sanitizedValue = $originalValue['value'];
            } elseif (isset($originalValue['delete']) && $originalValue['delete'] == 1) {
                // File marked for deletion
                $sanitizedValue = '';
            }
            
            // Overwrite the deep 'value' array with the simple string
            $sanitizedData[$section]['fields'][$fieldCode]['value'] = $sanitizedValue;
            $this->logger->info("Vnecoms Plugin: Sanitized '$path' to value: '$sanitizedValue'");
        }
    }
    
    // Pass the now-sanitized data (with strings instead of file arrays) to the original saveConfig method.
    return $proceed($scope, $scopeId, $sanitizedData);
}}