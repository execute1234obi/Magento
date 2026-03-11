<?php
return [
    'backend' => [
        'frontName' => 'admin'
    ],
    'cache' => [
        'graphql' => [
            'id_salt' => '7qfvNTvSuJVIxxcBqI8NpjOfxqj1dBAF'
        ],
        'frontend' => [
            'default' => [
                'id_prefix' => 'c46_'
            ],
            'page_cache' => [
                'id_prefix' => 'c46_'
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => '323a0a90682896516678de2911f9a100'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'magento_gcc_db',
                'dbname' => 'magento_gcc',
                'username' => 'root',
                'password' => 'magento@123',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'files'
    ],
    'lock' => [
        'provider' => 'db'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1
    ],
    'downloadable_domains' => [
        'localhost'
    ],
    'install' => [
        'date' => 'Thu, 17 Jul 2025 18:22:04 +0000'
    ],
    'elasticsearch' => [
        'host' => 'magento_es',
        'port' => '9200',
        'enable_auth' => '0',
        'index_prefix' => 'magento2',
        'enable_snapshot' => '0',
        'indexer' => [
            'catalogsearch' => 'elasticsearch7'
        ]
    ],
    'search' => [
        'engine' => 'elasticsearch7',
        'elasticsearch7_server_hostname' => 'magento_es',
        'elasticsearch7_server_port' => 9200,
        'elasticsearch7_index_prefix' => 'magento2',
        'elasticsearch7_enable_auth' => false
    ]
];
