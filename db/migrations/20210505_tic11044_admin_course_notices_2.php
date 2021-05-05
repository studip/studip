<?php
class Tic11044AdminCourseNotices2 extends Migration
{
    public function description()
    {
        return 'TIC #11044: Reworks datafield usage to course config';
    }

    public function up()
    {
        // Create course config
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES(
                    'COURSE_ADMIN_NOTICE', '', 'string', 'course', '',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Admins: Notiz zu einer Veranstaltung'
                  )";
        DBManager::get()->exec($query);

        // Migrate contents
        $query = "INSERT IGNORE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`)
                  SELECT 'COURSE_ADMIN_NOTICE', dfe.`range_id`, dfe.`content`, dfe.`mkdate`, dfe.`chdate`, ''
                  FROM `datafields_entries` AS dfe
                  JOIN `datafields` AS df USING (`datafield_id`)
                  WHERE df.`name` = 'Notiz zu einer Veranstaltung'
                    AND df.`object_type` = 'sem'";
        DBManager::get()->exec($query);

        // Delete datafield and values
        $query = "DELETE `datafields`, `datafields_entries`
                  FROM `datafields`
                  LEFT JOIN `datafields_entries` USING (`datafield_id`)
                  WHERE `datafields`.`name` = 'Notiz zu einer Veranstaltung'";
        DBManager::get()->execute($query);
    }

    public function down()
    {
        $id = md5(uniqid(__CLASS__, true));

        // Create datafield
        $query = "SELECT 1 + MAX(`priority`)
                  FROM `datafields`
                  WHERE `object_type` = 'sem'";
        $priority = DBManager::get()->fetchColumn($query) ?: 0;

        $query = "INSERT IGNORE INTO `datafields` (
                    `datafield_id`, `name`,
                    `object_type`, `object_class`,
                    `edit_perms`, `view_perms`,
                    `system`, `priority`,
                    `mkdate`, `chdate`,
                    `type`, `typeparam`,
                    `is_required`, `is_userfilter`,
                    `description`
                  ) VALUES (
                      :id, 'Notiz zu einer Veranstaltung',
                      'sem', NULL,
                      'admin', 'admin',
                      1, :priority,
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                      'textarea', '',
                      0, 0,
                      'Enthält etwaige Notizen von Admins zu Veranstaltungen'
                  )";
        DBManager::get()->execute($query, [
            ':id'       => $id,
            ':priority' => $priority,
        ]);

        // Migrate contents
        $query = "INSERT IGNORE INTO `datafields_entries` (`datafield_id`, `range_id`, `content`, `mkdate`, `chdate`)
                  SELECT :id, `range_id`, `value`, `mkdate`, `chdate`
                  FROM `config_values`
                  WHERE `field` = 'COURSE_ADMIN_NOTICE'";
        DBManager::get()->exec($query);

        // Remove course config
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `config`.`field` = 'ADMIN_COURSE_NOTICE'";
        DBManager::get()->execute($query);
    }
}
