<?php

class Step00192PageLayout extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'remove obsolete HTML_HEAD_TITLE config setting';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE field = 'HTML_HEAD_TITLE'");
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();
        $time = time();

        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :section, $time, $time, :description)
                ");

        $stmt->execute([
            'name' => 'HTML_HEAD_TITLE',
            'description' => 'Angezeigter Titel in der Kopfzeile des Browsers',
            'section' => 'global',
            'type' => 'string',
            'value' => 'Stud.IP'
        ]);
    }
}
