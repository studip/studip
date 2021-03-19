<?php
class ChangeBlubberThreadFollowing extends Migration
{
    public function description()
    {
        return 'Change blubber following from an opt-out to an opt-in mechanism (see ticket #11254)';
    }

    public function up()
    {
        // Alter table to reflect new state
        $query = "RENAME TABLE `blubber_threads_unfollow` TO `blubber_threads_followstates`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `blubber_threads_followstates`
                  ADD COLUMN `state` ENUM('followed', 'unfollowed') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'unfollowed' AFTER `user_id`";
        DBManager::get()->exec($query);

        // Insert commented users from global stream
        $query = "INSERT IGNORE INTO `blubber_threads_followstates`
                  SELECT DISTINCT 'global', `user_id`, 'followed', UNIX_TIMESTAMP()
                  FROM `blubber_comments`
                  WHERE `thread_id` = 'global'
                    AND NOT EXISTS (
                        SELECT 1
                        FROM `blubber_threads_followstates`
                        WHERE `blubber_comments`.`user_id` = `blubber_threads_followstates`.`user_id`
                          AND `blubber_threads_followstates`.`thread_id` = 'global'
                          AND `blubber_threads_followstates`.`state` = 'unfollowed'
                    )";

        // Create config entries
        $query = "INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'BLUBBER_GLOBAL_THREAD_OPTOUT',
            ':description' => 'Gibt an, ob beim globalen Blubber Thread ein Opt-Out-Verfahren genutzt werden soll',
            ':section'     => 'global',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);

        // Activate added routes
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/app/routes/Blubber.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Blubber());
    }

    public function down()
    {
        // Remove config entry
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'BLUBBER_GLOBAL_THREAD_OPTOUT'";
        DBManager::get()->exec($query);

        // Clean follow states table
        $query = "DELETE FROM `blubber_threads_followstates`
                  WHERE `state` = 'followed'";
        DBManager::get()->exec($query);

        // Drop state
        $query = "ALTER TABLE `blubber_threads_followstates`
                  DROP COLUMN `state`";
        DBManager::get()->exec($query);

        // Rename table
        $query = "RENAME TABLE `blubber_threads_followstates` TO `blubber_threads_unfollow`";
        DBManager::get()->exec($query);
    }
}
