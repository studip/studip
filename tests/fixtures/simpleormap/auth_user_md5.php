<?php
$result =  [
  0 =>
   [
    'name' => 'user_id',
    'type' => 'varchar(32)',
    'null' => 'NO',
    'key' => 'PRI',
    'default' => '',
    'extra' => '',
  ],
  1 =>
   [
    'name' => 'username',
    'type' => 'varchar(64)',
    'null' => 'NO',
    'key' => 'UNI',
    'default' => '',
    'extra' => '',
  ],
  2 =>
   [
    'name' => 'password',
    'type' => 'varchar(32)',
    'null' => 'NO',
    'key' => '',
    'default' => '',
    'extra' => '',
  ],
  3 =>
   [
    'name' => 'perms',
    'type' => 'enum(\'user\',\'autor\',\'tutor\',\'dozent\',\'admin\',\'root\')',
    'null' => 'NO',
    'key' => 'MUL',
    'default' => 'user',
    'extra' => '',
  ],
  4 =>
   [
    'name' => 'Vorname',
    'type' => 'varchar(64)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  5 =>
   [
    'name' => 'Nachname',
    'type' => 'varchar(64)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  6 =>
   [
    'name' => 'Email',
    'type' => 'varchar(64)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  7 =>
   [
    'name' => 'validation_key',
    'type' => 'varchar(10)',
    'null' => 'NO',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  8 =>
   [
    'name' => 'auth_plugin',
    'type' => 'varchar(64)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  9 =>
   [
    'name' => 'locked',
    'type' => 'tinyint(1) unsigned',
    'null' => 'NO',
    'key' => '',
    'default' => '0',
    'extra' => '',
  ],
  10 =>
   [
    'name' => 'lock_comment',
    'type' => 'varchar(255)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  11 =>
   [
    'name' => 'locked_by',
    'type' => 'varchar(32)',
    'null' => 'YES',
    'key' => '',
    'default' => NULL,
    'extra' => '',
  ],
  12 =>
   [
    'name' => 'visible',
    'type' => 'enum(\'global\',\'always\',\'yes\',\'unknown\',\'no\',\'never\')',
    'null' => 'NO',
    'key' => '',
    'default' => 'unknown',
    'extra' => '',
  ],
  13 =>
   [
    'name' => 'csvdata',
    'type' => 'varchar(255)',
    'null' => 'NO',
    'key' => '',
    'default' => '1,3',
    'extra' => '',
  ],
  14 =>
   [
    'name' => 'jsondata',
    'type' => 'varchar(255)',
    'null' => 'NO',
    'key' => '',
    'default' => '[1,2]',
    'extra' => '',
  ],
];
