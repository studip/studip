<?php
class ConvertCronjobLogs extends Migration
{
    public function description()
    {
        return 'Converts serialized exceptions in cronjobs logs to their '
             . 'textual representation';
    }
    
    public function up()
    {
        // Limits the operations to chunks this size
        $LIMIT = 10000;

        // Remove logs of successful executions older than 2 weeks
        // and logs of erroneous executions older than 6 months
        $query = "DELETE FROM `cronjobs_logs`
                  WHERE (`exception` IS NULL
                         AND `executed` < UNIX_TIMESTAMP(NOW() - INTERVAL 2 WEEK))
                     OR (`exception` IS NOT NULL
                         AND `executed` < UNIX_TIMESTAMP(NOW() - INTERVAL 6 MONTH))";
        DBManager::get()->exec($query);

        // Quickly convert serialized NULL entries for exception column
        $query = "UPDATE `cronjobs_logs`
                  SET `exception` = NULL
                  WHERE `exception` = 'N;'";
        DBManager::get()->exec($query);

        // Convert all remaining logs
        do {
            $converted = CronjobLog::findEachBySQL(function ($entry) {
                $entry->exception = unserialize($entry->exception) ?: null;
                $entry->store();
                unset($entry);
            }, "exception RLIKE '^(N;|O:)' LIMIT {$LIMIT}");
        } while ($converted > 0);
    }

    public function down()
    {
        // Not neccessary
    }
}
