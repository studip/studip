<?php
class Tic11044AdminCourseNotices extends Migration
{
    public function description()
    {
        return 'TIC #11044: Adds neccessary datafield that stores notice for courses';
    }

    public function up()
    {
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
            ':id'       => md5(uniqid(__CLASS__, true)),
            ':priority' => $priority,
        ]);
    }

    public function down()
    {
        $query = "DELETE `datafields`, `datafields_entries`
                  FROM `datafields`
                  LEFT JOIN `datafields_entries` USING (`datafield_id`)
                  WHERE `datafields`.`name` = 'Notiz zu einer Veranstaltung'";
        DBManager::get()->execute($query);
    }
}
