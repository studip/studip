<?php
class RemoveInvalidModulUserAssignments extends Migration
{
    public function description()
    {
        return 'Removes invalid user assignemnts from table `mvv_contacts` and '
        . '`mvv_contacts_ranges` (see BIEST #9809)';
    }

    public function up()
    {
        $query = "DELETE `mvv_contacts`, `mvv_contacts_ranges`
                    FROM `mvv_contacts`INNER JOIN `mvv_contacts_ranges`
                    WHERE `mvv_contacts`.`contact_id` = `mvv_contacts_ranges`.`contact_id`
                        AND	`mvv_contacts`.`contact_status` = 'intern'
                        AND `mvv_contacts`.`contact_id` NOT IN (
                            SELECT `user_id`
                            FROM `auth_user_md5`
                        )";
        DBManager::get()->exec($query);
    }
}
