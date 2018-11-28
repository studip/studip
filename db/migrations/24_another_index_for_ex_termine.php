<?php

class AnotherIndexForExTermine extends Migration {

  function description() {
    return 'another performance improving index for table "ex_termine"';
  }

  function up() {
    $db = DBManager::get();

    $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $db->exec("ALTER TABLE `ex_termine` ADD INDEX `autor_id` ( `autor_id` )");

    $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);
  }

  function down() {
    $db = DBManager::get();

    $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $db->exec("ALTER TABLE `ex_termine` DROP INDEX `autor_id`");

    $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);
  }
}
