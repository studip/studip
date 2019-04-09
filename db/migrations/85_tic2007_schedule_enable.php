<?php

class Tic2007ScheduleEnable extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds global configuration to disable/enable the schedule';
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
            'name' => 'SCHEDULE_ENABLE',
            'description' => 'Schaltet ein oder aus, ob der Stundenplan global verfügbar ist.',
            'section' => 'modules',
            'range' => 'global',
            'type' => 'boolean',
            'value' => '1'
        ]);
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE field = 'SCHEDULE_ENABLE'");
    }
}