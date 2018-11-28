<?php
class DbCacheTable extends Migration
{
    public function description()
    {
        return 'add database table for simple cache';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('CREATE TABLE cache (
                   cache_key VARCHAR(255) COLLATE latin1_bin NOT NULL,
                   content MEDIUMBLOB NOT NULL,
                   expires INT(11) UNSIGNED NOT NULL,
                   PRIMARY KEY (cache_key)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC');

        StudipCacheFactory::getCache()->flush();
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE cache');
    }
}
