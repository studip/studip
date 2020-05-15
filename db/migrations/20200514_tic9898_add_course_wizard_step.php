<?php


class Tic9898AddCourseWizardStep extends Migration
{
    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT IGNORE INTO `coursewizardsteps`
            (`id`, `name`, `classname`, `number`, `enabled`, `mkdate`, `chdate`)
            VALUES
            (MD5('StudyAreasLVGroupsCombinedWizardStep'), 'Studienbereich oder LV-Gruppe', 'StudyAreasLVGroupsCombinedWizardStep', '3', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM `StudyAreasLVGroupsCombinedWizardStep`
            WHERE `id` = MD5('')"
        );
    }


    public function description()
    {
        return 'Adds a new course wizard step to assign at least a study area or a LV group.';
    }
}
