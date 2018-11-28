<?php
$result = Array (
    0 => Array
        (
            'name' => 'id',
            'null' => 'NO',
            'default' => '',
            'type' => 'int(11)',
            'extra' => 'auto_increment',
            'key' => 'PRI',
        ),

    1 => Array
        (
            'name' => 'object_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    2 => Array
        (
            'name' => 'context',
            'null' => 'NO',
            'default' => '',
            'type' => "enum('system','course','institute','user')",
            'extra' => '',
        ),

    3 => Array
        (
            'name' => 'context_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    4 => Array
        (
            'name' => 'provider',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    5 => Array
        (
            'name' => 'actor_type',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    6 => Array
        (
            'name' => 'actor_id',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    7 => Array
        (
            'name' => 'verb',
            'null' => 'NO',
            'default' => 'experienced',
            'type' => "enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided')",
            'extra' => '',
        ),

    8 => Array
        (
            'name' => 'content',
            'null' => 'YES',
            'default' => '',
            'type' => 'text',
            'extra' => '',
        ),

    9 => Array
        (
            'name' => 'object_type',
            'null' => 'NO',
            'default' => '',
            'type' => 'varchar(255)',
            'extra' => '',
        ),

    10 => Array
        (
            'name' => 'mkdate',
            'null' => 'NO',
            'default' => '',
            'type' => 'int(11)',
            'extra' => '',
        )

);