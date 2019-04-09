<?php
$result =  [
    0 => 
        [
            'name' => 'id',
            'null' => 'NO',
            'default' => '',
            'type' => 'int(11)',
            'extra' => 'auto_increment',
            'key' => 'PRI',
        ],

    1 => 
        [
            'name' => 'object_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    2 => 
        [
            'name' => 'context',
            'null' => 'NO',
            'default' => '',
            'type' => "enum('system','course','institute','user')",
            'extra' => '',
        ],

    3 => 
        [
            'name' => 'context_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    4 => 
        [
            'name' => 'provider',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    5 => 
        [
            'name' => 'actor_type',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    6 => 
        [
            'name' => 'actor_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    7 => 
        [
            'name' => 'verb',
            'null' => 'NO',
            'default' => 'experienced',
            'type' => "enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided')",
            'extra' => '',
        ],

    8 => 
        [
            'name' => 'content',
            'null' => 'YES',
            'default' => '',
            'type' => 'text',
            'extra' => '',
        ],

    9 => 
        [
            'name' => 'object_type',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ],

    10 => 
        [
            'name' => 'mkdate',
            'null' => 'NO',
            'default' => '',
            'type' => 'int(11)',
            'extra' => '',
        ]

];