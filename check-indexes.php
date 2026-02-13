<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

/** @var \Mirasvit\Search\Repository\IndexRepository $indexRepository */
$indexRepository = $objectManager->get(\Mirasvit\Search\Repository\IndexRepository::class);

$indexes = $indexRepository->getCollection();

foreach ($indexes as $index) {
    echo $index->getIdentifier() . ' => ' . $index->getTitle() . PHP_EOL;
}
