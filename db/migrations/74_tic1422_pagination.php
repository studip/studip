<?php

class Tic1422Pagination extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'add number of entries per page';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();
        $time = time();

        $stmt = $db->prepare("
                INSERT INTO config
                    (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, $time, $time, :description)
                ");

        $stmt->execute([
            'name' => 'ENTRIES_PER_PAGE',
            'description' => 'Anzahl von Einträgen pro Seite',
            'section' => 'global',
            'range' => 'global',
            'type' => 'integer',
            'value' => 20
        ]);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE field = 'ENTRIES_PER_PAGE'");
    }
}
