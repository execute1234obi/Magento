<?php
use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

// Initialize Magento framework
$state = $obj->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('frontend');
} catch (\Magento\Framework\Exception\LocalizedException $e) {
    // Area code already set
}

// Load vendor collection using EAV model
/** @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory $collectionFactory */
$collectionFactory = $obj->get(\Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory::class);
$collection = $collectionFactory->create();
$collection->addAttributeToSelect(['vendor_name', 'description']);
$collection->setPageSize(100); // Optional limit

echo "Loaded Vendors:\n";
foreach ($collection as $vendor) {
    echo $vendor->getId() . ' - ' . $vendor->getVendorName() . ' | ' . $vendor->getDescription() . PHP_EOL;
}