<?php
/**
 * clean_object_user_visits.php.
 *
 * Core cronjob that removes old entries from the table "object_user_visits".
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since  3.3
 */

class CleanObjectUserVisits extends CronJob
{
    /**
     * Return the name of this cronjob.
     *
     * @return String containing the name
     */
    public static function getName()
    {
        return _('Entfernt alte Einträge aus der Tabelle "object_user_visits"');
    }

    /**
     * Returns the description for this cronjob.
     *
     * @return String containing the description
     */
    public static function getDescription()
    {
        return _('Um die Tabelle "object_user_visits" schmal zu halten, werden alte Einträge nach einem definierten Zeitraum entfernt.');
    }

    /**
     * Returns the defined configuration parameters for this cronjob.
     *
     * @return Array with defined parameters
     */
    public static function getParameters()
    {
        return [];
    }

    /**
     * Executes the cronjob.
     *
     * @param mixed $last_result Result returned from the last execution
     * @param Array $parameters  Defined parameters
     */
    public function execute($last_result, $parameters = [])
    {
        if (Config::get()->NEW_INDICATOR_THRESHOLD) {
            $query = "DELETE FROM `object_user_visits`
                      WHERE GREATEST(`visitdate`, `last_visitdate`) < UNIX_TIMESTAMP(NOW() - INTERVAL :expires DAY)";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':expires', (int) Config::get()->NEW_INDICATOR_THRESHOLD, PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
