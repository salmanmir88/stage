<?php
return [
    'cache' => [
        'graphql' => [
            'id_salt' => 'd2uScHtIJHjqgkVHg6luH14K8vyyZNwZ'
        ],
        'frontend' => [
            'default' => [
                'id_prefix' => 'f73_',
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '127.0.0.1',
                    'database' => '0',
                    'port' => '6379',
                    'password' => '9g5HqkwXz2CVL2VlKdejVTxY+wcF7mEP08ZBJ1ZUq4wWTnqC3UDBI9fyoUMpIRvyziBFXE8yC6033a4e',
                    'compress_data' => '1',
                    'compression_lib' => ''
                ]
            ],
            'page_cache' => [
                'id_prefix' => 'f73_',
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '127.0.0.1',
                    'database' => '1',
                    'port' => '6379',
                    'password' => '9g5HqkwXz2CVL2VlKdejVTxY+wcF7mEP08ZBJ1ZUq4wWTnqC3UDBI9fyoUMpIRvyziBFXE8yC6033a4e',
                    'compress_data' => '1',
                    'compression_lib' => ''
                ]
            ]
        ],
        'allow_parallel_generation' => true
    ],
    'session' => [
        'save' => 'files'
    ],
    'cache_types' => [
        'compiled_config' => 1,
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'amasty_shopby' => 1,
        'ox_megamenu' => 1
    ],
    'backend' => [
        'frontName' => 'admin'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => '9ae6ee269de05adc66f50a8b08e23849'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'kpopstage',
                'username' => 'root',
                'password' => 'bDu=#Cluf6sgdjsg',
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
    'MAGE_MODE' => 'developer',
    'lock' => [
        'provider' => 'db'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'install' => [
        'date' => 'Wed, 17 Jul 2024 09:11:12 +0000'
    ],
    'http_cache_hosts' => [
        [
            'host' => 'localhost'
        ]
    ]
];
