<?php

class LitListContentNoteFromVarcharToText extends Migration {

  function description() {
    return 'lit_list_content::note from VARCHAR to TEXT';
  }

  function up() {
    $db = DBManager::get();

    $db->exec("ALTER TABLE `lit_list_content` CHANGE `note` `note` TEXT NULL DEFAULT NULL");
  }

  function down() {
    $db = DBManager::get();
    $db->exec("ALTER TABLE `lit_list_content` CHANGE `note` `note` VARCHAR( 255 ) NULL DEFAULT NULL");
  }
}
