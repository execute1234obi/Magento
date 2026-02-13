<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

// create your source model
$countrySource = $objectManager->create(\Vendor\VendorsVerification\Model\Source\Countries::class);

// get all options
$options = $countrySource->getAllOptions();

echo "<pre>";
print_r($options);
echo "</pre>";
