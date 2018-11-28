<?php
$result = array (
  0 =>
  array (
    'name' => 'schedule_id',
    'type' => 'char(32)',
    'null' => 'NO',
    'key' => 'PRI',
    'default' => '',
    'extra' => '',
  ),
  1 =>
  array (
    'name' => 'task_id',
    'type' => 'char(32)',
    'null' => 'NO',
    'key' => 'MUL',
    'default' => '',
    'extra' => '',
  ),
  2 =>
  array (
    'name' => 'active',
    'type' => 'tinyint(1)',
    'null' => 'NO',
    'key' => '',
    'default' => '0',
    'extra' => '',
  ),
  3 =>
  array (
    'name' => 'title',
    'type' => 'varchar(255)',
    'null' => 'NO',
    'key' => '',
    'default' => '',
    'extra' => '',
  ),
  4 =>
  array (
    'name' => 'description',
    'type' => 'varchar(4096)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  5 =>
  array (
    'name' => 'parameters',
    'type' => 'text',
    'null' => 'YES',
    'key' => '',
    'default' => '',
    'extra' => '',
  ),
  6 =>
  array (
    'name' => 'priority',
    'type' => 'enum(\'low\',\'normal\',\'high\')',
    'null' => 'NO',
    'key' => '',
    'default' => 'normal',
    'extra' => '',
  ),
  7 =>
  array (
    'name' => 'type',
    'type' => 'enum(\'periodic\',\'once\')',
    'null' => 'NO',
    'key' => '',
    'default' => 'periodic',
    'extra' => '',
  ),
  8 =>
  array (
    'name' => 'minute',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  9 =>
  array (
    'name' => 'hour',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  10 =>
  array (
    'name' => 'day',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  11 =>
  array (
    'name' => 'month',
    'type' => 'tinyint(2)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  12 =>
  array (
    'name' => 'day_of_week',
    'type' => 'tinyint(1) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  13 =>
  array (
    'name' => 'next_execution',
    'type' => 'int(11) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  14 =>
  array (
    'name' => 'last_execution',
    'type' => 'int(11) unsigned',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ),
  15 =>
  array (
    'name' => 'last_result',
    'type' => 'text',
    'null' => 'YES',
    'key' => '',
    'default' => '',
    'extra' => '',
  ),
  16 =>
  array (
    'name' => 'execution_count',
    'type' => 'bigint(20) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ),
  17 =>
  array (
    'name' => 'mkdate',
    'type' => 'int(11) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ),
  18 =>
  array (
    'name' => 'chdate',
    'type' => 'int(11) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => 0,
    'extra' => '',
  ),
);
