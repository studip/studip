<?php
/**
 * Provides a GUI for defining admission rule compatibility
 * and stores entries in database.
 *
 * @author  Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 *
 * @see https://develop.studip.de/trac/ticket/6655
 */
class TIC6655AdmissionRuleCompatibility extends Migration
{
    public function description()
    {
        return 'Compatibility between admission rules is now stored in database and can be configured via GUI.';
    }

    public function up()
    {
        // Table for rule definitions.
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `admissionrule_compat` (
                `rule_type` VARCHAR(32) NOT NULL,
                `compat_rule_type` VARCHAR(32) NOT NULL,
                `mkdate` int(11) NOT NULL DEFAULT 0,
                `chdate` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`rule_type`, `compat_rule_type`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        // Initial DB entries, taken from former class variables ($allowed_combinations).
        $compat = [
            'ConditionalAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'LimitedAdmission',
                'ParticipantRestrictedAdmission',
                'PasswordAdmission',
                'PreferentialAdmission',
                'TimedAdmission'
            ],
            'CourseMemberAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'LimitedAdmission',
                'ParticipantRestrictedAdmission',
                'PasswordAdmission',
                'PreferentialAdmission',
                'TimedAdmission'
            ],
            'LimitedAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'ParticipantRestrictedAdmission',
                'PasswordAdmission',
                'PreferentialAdmission',
                'TimedAdmission'
            ],
            'ParticipantRestrictedAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'LimitedAdmission',
                'PreferentialAdmission',
                'TimedAdmission'
            ],
            'PasswordAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'PreferentialAdmission',
                'TimedAdmission',
            ],
            'PreferentialAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'LimitedAdmission',
                'ParticipantRestrictedAdmission',
                'PasswordAdmission',
                'TimedAdmission'
            ],
            'TimedAdmission' => [
                'ConditionalAdmission',
                'CourseMemberAdmission',
                'LimitedAdmission',
                'ParticipantRestrictedAdmission',
                'PasswordAdmission',
                'PreferentialAdmission'
            ]
        ];

        $stmt = DBManager::get()->prepare("INSERT IGNORE INTO `admissionrule_compat`
                        (`rule_type`, `compat_rule_type`, `mkdate`, `chdate`)
                 VALUES (:ruletype, :compat, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");

        foreach ($compat as $type => $compat_types) {
            foreach ($compat_types as $c) {
                $stmt->execute(['ruletype' => $type, 'compat' => $c]);
            }
        }
    }

    public function down()
    {
        // Remove rule data tables.
        DBManager::get()->exec("DROP TABLE IF EXISTS `admissionrule_compat`");
    }
}
