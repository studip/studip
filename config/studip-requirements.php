<?php
return [
    'php'   => [
        'version' => '7.0',
        'modules' => [
            'PDO'       => true,
            'pdo_mysql' => true,
            'gettext'   => true,
            'session'   => true,
            'curl'      => true,
            'gd'        => true,
            'mbstring'  => true,
            'json'      => true,
            'ftp'       => true,
            'zip'       => true,
            'xsl'       => 'Export',
            'xml'       => 'Export',
            'ldap'      => 'LDAP-Nutzerverwaltung',
            'dom'       => 'Literaturverwaltung',
            'mysqlnd'   => 'Globale Suche',
            'mysqli'    => 'Globale Suche',
        ],
        'settings' => [
            'short_open_tag' => true,
            'allow_url_fopen' => true,
            'default_charset' => [
                'value' => 'UTF-8',
                'allow_empty' => true,
            ],
            'mbstring.internal_encoding' => [
                'value' => 'UTF-8',
                'allow_empty' => true,
            ],
            'error_reporting' => [
                'value' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED,
                'cmp' => '>='
            ],
            'max_input_vars' => [
                'value' => 10000,
                'cmp'   => '>=',
            ],
            'max_execution_time' => [
                'value'       => 300,
                'cmp'         => '>=',
                'allow_empty' => true,
            ],
            'upload_max_filesize' => [
                'value' => '8M',
                'cmp'   => '>=',
                'unit'  => true,
            ],
            'memory_limit' => [
                'value' => '128M',
                'cmp'   => '>=',
                'unit'  => true,
            ],
            'post_max_size' => [
                'value' => '9M',
                'cmp'   => '>=',
                'unit'  => true,
            ],
        ],
    ],
    'mysql' => [
        'version' => '5.7.6',
        'settings' => [
            'innodb_file_per_table' => true,
            'innodb_file_format' => [
                'value' => 'Barracuda',
                'assume' => '5.7.7',
            ],
            'innodb_large_prefix' => [
                'value'  => true,
                'assume' => '5.7.7',
            ],
            'sql_mode' => [
                'value'    => 'NO_ENGINE_SUBSTITUTION',
                'contains' => true,
            ]
        ],
    ],
    'writable' => [
        'data/upload_doc',
        'data/assets_cache',
        'data/media_cache',
        'public/plugins_packages',
    ],
];
