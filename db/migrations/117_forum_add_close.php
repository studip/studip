<?php

class ForumAddClose extends Migration {

    function description() {
        return 'Thread can now be closed.';
    }

    function up() {
        DBManager::get()->exec("ALTER TABLE forum_entries
            ADD `closed` TINYINT( 1 ) NOT NULL DEFAULT '0';");
    }

    function down() {
        DBManager::get()->exec("ALTER TABLE `forum_entries`
            DROP `closed`;");
    }

}

?>
