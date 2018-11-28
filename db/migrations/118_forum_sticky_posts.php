<?php

class ForumStickyPosts extends Migration {

    function description() {
        return 'Posts can now be marked as sticky.';
    }

    function up() {
        DBManager::get()->exec("ALTER TABLE forum_entries
            ADD `sticky` INT(1) NOT NULL DEFAULT '0';");
    }

    function down() {
        DBManager::get()->exec("ALTER TABLE `forum_entries`
            DROP `sticky`;");
    }

}

?>
