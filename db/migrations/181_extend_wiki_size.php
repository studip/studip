<?php
class ExtendWikiSize extends Migration
{
    public function description()
    {
        return 'Increase max length of wiki like content (wiki, scm, cv, publications) from 64k to 16M';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE scm CHANGE tab_name tab_name varchar(255) NOT NULL DEFAULT '',
                                   CHANGE content content mediumtext NOT NULL");
        $db->exec("ALTER TABLE user_info CHANGE lebenslauf lebenslauf mediumtext NOT NULL,
                                         CHANGE publi publi mediumtext NOT NULL");
        $db->exec("ALTER TABLE wiki CHANGE keyword keyword varchar(255) BINARY NOT NULL DEFAULT '',
                                    CHANGE body body mediumtext NOT NULL");
        $db->exec("ALTER TABLE wiki_links CHANGE from_keyword from_keyword varchar(255) BINARY NOT NULL DEFAULT '',
                                          CHANGE to_keyword to_keyword varchar(255) BINARY NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE wiki_locks CHANGE keyword keyword varchar(255) BINARY NOT NULL DEFAULT ''");
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("ALTER TABLE scm CHANGE tab_name tab_name varchar(20) NOT NULL DEFAULT 'Info',
                                   CHANGE content content text");
        $db->exec("ALTER TABLE user_info CHANGE lebenslauf lebenslauf text,
                                         CHANGE publi publi text NOT NULL");
        $db->exec("ALTER TABLE wiki CHANGE keyword keyword varchar(128) BINARY NOT NULL DEFAULT '',
                                    CHANGE body body text");
        $db->exec("ALTER TABLE wiki_links CHANGE from_keyword from_keyword char(128) BINARY NOT NULL DEFAULT '',
                                          CHANGE to_keyword to_keyword char(128) BINARY NOT NULL DEFAULT ''");
        $db->exec("ALTER TABLE wiki_locks CHANGE keyword keyword varchar(128) BINARY NOT NULL DEFAULT ''");
    }
}
