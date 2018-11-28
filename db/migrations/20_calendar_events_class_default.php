<?php

class CalendarEventsClassDefault extends Migration {

  function description() {
    return 'table calendar_events field class should default to "PRIVATE" '.
           'instead of "PUBLIC"';
  }

  function up() {
    $db = DBManager::get();
    $db->exec("ALTER TABLE `calendar_events` CHANGE `class` `class` ".
              "ENUM( 'PUBLIC', 'PRIVATE', 'CONFIDENTIAL' ) ".
              "NOT NULL DEFAULT 'PRIVATE'");
  }

  function down() {
    $db = DBManager::get();
    $db->exec("ALTER TABLE `calendar_events` CHANGE `class` `class` ".
              "ENUM( 'PUBLIC', 'PRIVATE', 'CONFIDENTIAL' ) ".
              "NOT NULL DEFAULT 'PUBLIC'");
  }
}
