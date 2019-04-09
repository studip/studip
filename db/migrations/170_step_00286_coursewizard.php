<?php

require_once('lib/models/CourseWizardStepRegistry.php');

class Step00286CourseWizard extends Migration
{

    function description() {
        return 'new course creation wizard';
    }

    function up()
    {
        // Create table for step registry.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `coursewizardsteps` (
            `id` VARCHAR(32) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `classname` VARCHAR(255) NOT NULL UNIQUE,
            `number` TINYINT(1) NOT NULL,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`))");
        // Add the default steps:
        // Step 1: Basic data.
        if (!CourseWizardStepRegistry::findByClassName('BasicDataWizardStep')) {
            CourseWizardStepRegistry::registerStep('Grunddaten', 'BasicDataWizardStep', 1, true);
        }
        // Step 2: Study area assignment (there are course classes requiring this).
        if (!CourseWizardStepRegistry::findByClassName('StudyAreasWizardStep')) {
            CourseWizardStepRegistry::registerStep('Studienbereiche', 'StudyAreasWizardStep', 2, true);
        }
        // Add text template for studygroup acceptance to global config.
        if (!Config::get()->STUDYGROUP_ACCEPTANCE_TEXT) {
            DBManager::get()->execute("
                INSERT IGNORE INTO config
                    (`config_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                VALUES
                    (MD5(:name), :name, :value, 1, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
                ",
                [
                 'name' => 'STUDYGROUP_ACCEPTANCE_TEXT',
                 'value' => _('Die Moderatorinnen und ' .
                    'Moderatoren der Studiengruppe können Ihren ' .
                    'Aufnahmewunsch bestätigen oder ablehnen. Erst nach ' .
                    'Bestätigung erhalten Sie vollen Zugriff auf die ' .
                    'Gruppe.'),
                'type' => 'string',
                'range' => 'global',
                'section' => 'studygroups',
                'description' => _('Text, der angezeigt wird, wenn man sich ' .
                    'in eine zugriffsbeschränkte Studiengruppe eintragen möchte')
                ]
            );
        }
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `coursewizardsteps`");
    }

}
