<?php

class MoreIndexing extends Migration {

  function description() {
    return 'several additional keys to speed up queries';
  }

  function up() {
    $db = DBManager::get();

    $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $db->exec("ALTER TABLE `contact` ADD INDEX `user_id` ( `user_id` )");
    $db->exec("ALTER TABLE `datafields_entries` ADD INDEX `datafields_contents` (`datafield_id`,`content`(32) )");

    $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);
  }

  function down() {
    $db = DBManager::get();

    $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $db->exec("ALTER TABLE `datafields_entries` DROP INDEX `datafields_contents`");
    $db->exec("ALTER TABLE `auth_user_md5` DROP INDEX `user_id`");

    $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);
  }
}
