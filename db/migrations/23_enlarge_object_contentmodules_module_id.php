<?php

class EnlargeObjectContentmodulesModuleId extends Migration {

  function description() {
    return 'enlarge object_contentmodules::module_id from VARCHAR32 to VARCHAR255';
  }

  function up() {
    $db = DBManager::get();
    $db->exec("ALTER TABLE `object_contentmodules` CHANGE `module_id` `module_id` VARCHAR( 255 ) NOT NULL DEFAULT ''");
  }

  function down() {
    $db = DBManager::get();
    $db->exec("ALTER TABLE `object_contentmodules` CHANGE `module_id` `module_id` VARCHAR( 255 ) NULL DEFAULT NULL");
  }
}
