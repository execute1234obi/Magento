<?php

require 'vendor/autoload.php';

use Elastic\Elasticsearch\ClientBuilder;

$client = ClientBuilder::create()
    ->setHosts(['http://localhost:9200']) // important: include http://
    ->build();

try {
    $response = $client->info();
    print_r($response);
} catch (Exception $e) {
    echo 'Connection failed: ', $e->getMessage();
}
