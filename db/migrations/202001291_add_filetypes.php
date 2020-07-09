<?php
class AddFiletypes extends Migration
{
    public function description()
    {
        return "Adds special types of files to the filesystem.";
    }

    public function up()
    {
       DBManager::get()->exec("
            ALTER TABLE files
            ADD COLUMN `filetype` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT 'StandardFile' AFTER `name`,
            ADD COLUMN `metadata` TEXT NULL AFTER `size`
        ");
        DBManager::get()->exec('
            UPDATE files
                INNER JOIN file_urls ON (file_urls.file_id = files.id)
            SET files.metadata = JSON_OBJECT("url", file_urls.url, "access_type", file_urls.access_type),
                files.filetype = "URLFile"
        ');
        DBManager::get()->exec("
            DROP TABLE file_urls
        ");
        DBManager::get()->exec("
            ALTER TABLE files
            DROP COLUMN `storage`
        ");
    }


    public function down()
    {
        DBManager::get()->exec("
            CREATE TABLE `file_urls` (
                `file_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `url` varchar(4096) COLLATE utf8mb4_unicode_ci NOT NULL,
                `access_type` enum('proxy','redirect') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'proxy',
                PRIMARY KEY (`file_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
        ");
        DBManager::get()->exec("
            ALTER TABLE files
            DROP COLUMN `filetype`,
            DROP COLUMN `metadata`
        ");
    }
}
