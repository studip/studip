<?php
$result =  [
  0 =>
   [
    'name' => 'schedule_id',
    'type' => 'char(32)',
    'null' => 'NO',
    'key' => 'PRI',
    'default' => '',
    'extra' => '',
  ],
  1 =>
   [
    'name' => 'task_id',
    'type' => 'char(32)',
    'null' => 'NO',
    'key' => 'MUL',
    'default' => '',
    'extra' => '',
  ],
  2 =>
   [
    'name' => 'active',
    'type' => 'tinyint(1)',
    'null' => 'NO',
    'key' => '',
    'default' => '0',
    'extra' => '',
  ],
  3 =>
   [
    'name' => 'title',
    'type' => 'varchar(255)',
    'null' => 'NO',
    'key' => '',
    'default' => '',
    'extra' => '',
  ],
  4 =>
   [
    'name' => 'description',
    'type' => 'varchar(4096)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  5 =>
   [
    'name' => 'parameters',
    'type' => 'text',
    'null' => 'YES',
    'key' => '',
    'default' => '',
    'extra' => '',
  ],
  6 =>
   [
    'name' => 'priority',
    'type' => 'enum(\'low\',\'normal\',\'high\')',
    'null' => 'NO',
    'key' => '',
    'default' => 'normal',
    'extra' => '',
  ],
  7 =>
   [
    'name' => 'type',
    'type' => 'enum(\'periodic\',\'once\')',
    'null' => 'NO',
    'key' => '',
    'default' => 'periodic',
    'extra' => '',
  ],
  8 =>
   [
    'name' => 'minute',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  9 =>
   [
    'name' => 'hour',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  10 =>
   [
    'name' => 'day',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  11 =>
   [
    'name' => 'month',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  12 =>
   [
    'name' => 'day_of_week',
    'type' => 'tinyint(1) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  13 =>
   [
    'name' => 'next_execution',
    'type' => 'int(11) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  14 =>
   [
    'name' => 'last_execution',
    'type' => 'int(11) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  15 =>
   [
    'name' => 'last_result',
    'type' => 'text',
    'null' => 'YES',
    'key' => '',
    'default' => '',
    'extra' => '',
  ],
  16 =>
   [
    'name' => 'execution_count',
    'type' => 'bigint(20) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ],
  17 =>
   [
    'name' => 'mkdate',
    'type' => 'int(11) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ],
  18 =>
   [
    'name' => 'chdate',
    'type' => 'int(11) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ],
];
