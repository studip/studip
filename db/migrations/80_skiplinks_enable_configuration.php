<?php

class SkiplinksEnableConfiguration extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds a configuration for enabling skip links';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $options[] =
            [
            'name'        => 'SKIPLINKS_ENABLE',
            'type'        => 'boolean',
            'value'       => '',
            'range'       => 'user',
            'section'     => 'privacy',
            'description' => 'Wählen Sie diese Option, um Skiplinks beim ersten Drücken der Tab-Taste anzuzeigen (Systemdefault).'
            ];

        $stmt = DBManager::get()->prepare("
                INSERT IGNORE INTO config
                    (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ");

        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get()->exec("DELETE FROM config WHERE field = 'SKIPLINKS_ENABLE'");
    }
}
