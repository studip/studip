<?php
/**
 * Introduces terms admission rule.
 *
 * @author Niklas Dettmer <ndettmer@uos.de>
 * @license GPL2 or any later version
 * @since Stud.IP 5.0
 *
 * @see https://develop.studip.de/trac/ticket/8183
 */
class TIC8183TermsAdmission extends Migration
{
    public function description()
    {
        return 'introduces an admission rule that requires accepting terms of admission';
    }

    public function up()
    {
        $db = DBManager::get();

        // table for rule definitions
        $db->exec("CREATE TABLE IF NOT EXISTS termsadmissions (
                    rule_id varchar(32) COLLATE latin1_bin NOT NULL,
                    terms text NOT NULL,
                    mkdate int(11) NOT NULL DEFAULT 0,
                    chdate int(11) NOT NULL DEFAULT 0,
                    PRIMARY KEY (rule_id)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        // install rule to database
        $db->exec("INSERT IGNORE INTO admissionrules (id, ruletype, active, mkdate, path)
                   VALUES (0, 'TermsAdmission', 1, UNIX_TIMESTAMP(), 'lib/admissionrules/termsadmission')");

        // install allowed combinations
        $rules = [
            'ConditionalAdmission',
            'CourseMemberAdmission',
            'LimitedAdmission',
            'ParticipantRestrictedAdmission',
            'PreferentialAdmission',
            'TimedAdmission'
        ];

        $stmt = DBManager::get()->prepare('INSERT IGNORE INTO admissionrule_compat
                 VALUES (:ruletype, :compat, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())');

        foreach ($rules as $rule) {
            $stmt->execute(['ruletype' => 'TermsAdmission', 'compat' => $rule]);
            $stmt->execute(['ruletype' => $rule, 'compat' => 'TermsAdmission']);
        }
    }

    public function down()
    {
        $db = DBManager::get();

        // remove allowed combinations
        $db->exec("DELETE FROM admissionrule_compat WHERE rule_type = 'TermsAdmission' OR compat_rule_type = 'TermsAdmission'");

        // remove entry in admission rule registry
        $db->exec("DELETE FROM admissionrules WHERE ruletype = 'TermsAdmission'");

        // remove rule data table
        $db->exec("DROP TABLE termsadmissions");
    }
}
