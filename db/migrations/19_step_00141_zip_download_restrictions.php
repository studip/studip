<?php

class Step00141ZipDownloadRestrictions extends Migration
{
    function description() {
        return 'config entries ZIP_DOWNLOAD_MAX_FILES and ZIP_DOWNLOAD_MAX_SIZE';
    }
    
    function up() {
        $db = DBManager::get();
        $db->exec("INSERT IGNORE INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
                VALUES (MD5( 'ZIP_DOWNLOAD_MAX_FILES' ) , '', 'ZIP_DOWNLOAD_MAX_FILES', '100', '1', 'integer', 'global', '', '0', UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) , 'Die maximale Anzahl an Dateien, die gezippt heruntergeladen werden kann', '', ''
                )");
        $db->exec("INSERT IGNORE INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
                VALUES (MD5( 'ZIP_DOWNLOAD_MAX_SIZE' ) , '', 'ZIP_DOWNLOAD_MAX_SIZE', '100', '1', 'integer', 'global', '', '0', UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) , 'Die maximale Größe aller Dateien, die zusammen in einem Zip heruntergeladen werden kann (in Megabytes).', '', ''
                )");
    }
    
    function down() {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE config_id IN (MD5('ZIP_DOWNLOAD_MAX_FILES'),MD5('ZIP_DOWNLOAD_MAX_SIZE'))");
    }
}
?>
