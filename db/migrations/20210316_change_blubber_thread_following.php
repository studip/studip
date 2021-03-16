<?php
class ChangeBlubberThreadFollowing extends Migration
{
    public function description()
    {
        return 'Change blubber following from an opt-out to an opt-in mechanism (see ticket #11254)';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `blubber_threads_follow` (
                    `thread_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`thread_id`, `user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `blubber_threads_follow`
                  SELECT DISTINCT `thread_id`, `user_id`, UNIX_TIMESTAMP()
                  FROM (
                      SELECT `thread_id`, `user_id`
                      FROM `blubber_threads`

                      UNION ALL

                      SELECT `thread_id`, `user_id`
                      FROM `blubber_comments`
                      WHERE `external_contact` = 0
                  ) AS tmp
                  WHERE `user_id` != ''
                    AND NOT EXISTS (
                      SELECT 1
                      FROM `blubber_threads_unfollow`
                      WHERE tmp.`thread_id` = `blubber_threads_unfollow`.`thread_id`
                        AND tmp.`user_id` = `blubber_threads_unfollow`.`user_id`
                  )";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `blubber_threads_unfollow`";
        DBManager::get()->exec($query);

        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/app/routes/Blubber.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Blubber());
    }

    public function down()
    {
        $query = "CREATE TABLE IF NOT EXISTS `blubber_threads_unfollow` (
                    `thread_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`thread_id`, `user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `blubber_threads_unfollow`
                  SELECT DISTINCT `thread_id`, `user_id`, UNIX_TIMESTAMP()
                  FROM (
                      SELECT `thread_id`, `user_id`
                      FROM `blubber_threads`

                      UNION ALL

                      SELECT `thread_id`, `user_id`
                      FROM `blubber_comments`
                      WHERE `external_contact` = 0
                  ) AS tmp
                  WHERE `user_id` != ''
                    AND NOT EXISTS (
                      SELECT 1
                      FROM `blubber_threads_follow`
                      WHERE tmp.`thread_id` = `blubber_threads_unfollow`.`thread_id`
                        AND tmp.`user_id` = `blubber_threads_unfollow`.`user_id`
                    )";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `blubber_threads_follow`";
        DBManager::get()->exec($query);

        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/app/routes/Blubber.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Blubber());
    }
}
