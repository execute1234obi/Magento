<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();

$state = $om->get(\Magento\Framework\App\State::class);
$state->setAreaCode('adminhtml');

$report = $om->get(
    \Business\VisitorcountryReport\Model\ResourceModel\Report\VisitorCountry::class
);

$report->aggregate();

echo "Visitor Country aggregation executed\n";
