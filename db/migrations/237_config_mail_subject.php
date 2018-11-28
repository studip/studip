<?php
class ConfigMailSubject extends Migration
{
    public function description()
    {
        return 'add config options for MAIL_USE_SUBJECT_PREFIX and NOTIFY_ON_WAITLIST_ADVANCE';
    }

    public function up()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('INSERT INTO config (field, value, type, section, mkdate, chdate, description)
                              VALUES (:name, :value, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)');
        $stmt->execute([
            'name'        => 'MAIL_USE_SUBJECT_PREFIX',
            'description' => 'Stellt dem Titel von per Mail versandten Nachrichten den Wert von UNI_NAME_CLEAN voran.',
            'section'     => 'global',
            'type'        => 'boolean',
            'value'       => '1'
        ]);
        $stmt->execute([
            'name'        => 'NOTIFY_ON_WAITLIST_ADVANCE',
            'description' => 'Versendet Nachrichten an Teilnehmer bei jeder Änderung der Position auf der Warteliste',
            'section'     => 'global',
            'type'        => 'boolean',
            'value'       => '1'
        ]);
    }

    public function down()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('DELETE config, config_values FROM config LEFT JOIN config_values USING(field) WHERE field = ?');
        $stmt->execute(['MAIL_USE_SUBJECT_PREFIX']);
        $stmt->execute(['NOTIFY_ON_WAITLIST_ADVANCE']);
    }
}
