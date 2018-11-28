<?php

class WysiwygConfigOption extends Migration
{
    function description()
    {
        return 'adds config option for wysiwyg editor';
    }

    function up()
    {
        $stmt = DBManager::get()->exec("
            INSERT INTO config
            (config_id, field, value, is_default, `type`, `range`, section, mkdate, chdate, description, comment)
            VALUES
            ('e8cd96580149cde65ad69b6cf18d5c4A', 'WYSIWYG', '0', 1, 'boolean', 'global', 'global', 
             UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), 'Aktiviert den WYSIWYG Editor im JavaScript.', '')
            ");
    }

    function down()
    {
    }
}
