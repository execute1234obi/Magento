<?php
use Magento\Framework\App\Bootstrap;

require 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$appState = $objectManager->get(\Magento\Framework\App\State::class);
try {
    $appState->setAreaCode('frontend');
} catch (\Magento\Framework\Exception\LocalizedException $e) {
    // Area code is already set
}

$client = $objectManager->get('Magento\Elasticsearch8\Model\Client\Elasticsearch')->getClient();

try {
    if ($client->ping()) {
        echo "✅ Elasticsearch is reachable.\n";
    } else {
        echo "❌ Elasticsearch is NOT reachable.\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
