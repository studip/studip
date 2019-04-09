<?php

class Step00275Plus extends Migration {

    function description() {
        return 'configuration "plus"';
    }

    function up() {
        DBManager::get()->execute("
            INSERT IGNORE INTO config
            (config_id, field, value, is_default, `type`, `range`, mkdate, chdate, description, comment)
            VALUES
            (MD5(:name), :name, :value, 1, 'array', 'user', UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description, '')
            ", ['name' => 'PLUS_SETTINGS',
                     'value' => '[]',
                     'description' => 'Nutzer Konfiguration für Plusseite']);
    }

    function down() {
        DBManager::get()->exec("DELETE FROM config WHERE field = 'PLUS_SETTINGS'");
        DBManager::get()->exec("DELETE FROM user_config WHERE field = 'PLUS_SETTINGS'");
    }

}

