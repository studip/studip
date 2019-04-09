<?php

class ConfigFilesystemMulticopyEnable extends Migration {

    function description() {
        return 'Inserts a new config-variable to enable or disable multicopy for teachers.';
    }

    function up() {
        $options[] =
            [
            'name'        => 'FILESYSTEM_MULTICOPY_ENABLE',
            'type'        => 'boolean',
            'value'       => 1,
            'section'     => '',
            'description' => 'Soll es erlaubt sein, das Dozenten Ordner oder Dateien in mehrere Veranstaltungen bzw. Institute verschieben oder kopieren dürfen?'
            ];

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, UNIX_TIMESTAMP(),  UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    function down() {
        $db = DBManager::get()->exec("DELETE FROM config WHERE field = 'FILESYSTEM_MULTICOPY_ENABLE'");
    }
}
