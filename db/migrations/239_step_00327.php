<?php

class Step00327 extends Migration {

    public function up()
    {
        $r = DBManager::Get()->exec("UPDATE `config` SET `field` = 'PRIVACY_PERM', `section` = 'privacy' WHERE `field` = 'DATENSCHUTZ_PERM'");
        if (!$r) {
            DBManager::Get()->exec("INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('PRIVACY_PERM', 'root', 'string', 'global', 'privacy', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Rechtestufe zum Datenzugriff')");
        } else {
            // plugin now obsolete
            DBManager::Get()->exec("UPDATE `plugins` SET `enabled` = 'no' WHERE `plugins`.`pluginclassname` LIKE 'DatenschutzPlugin';");
        }

        $r = DBManager::Get()->exec("UPDATE `config` SET `field` = 'PRIVACY_CONTACT', `section` = 'privacy' WHERE `config`.`field` = 'DATENSCHUTZ_CONTACT'");
        if (!$r) {
            DBManager::Get()->exec("INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('PRIVACY_CONTACT', 'root@studip', 'string', 'global', 'privacy', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Username der Kontaktperson zum Datenschutz')");
        }

        // Datenschutzerklärung
        $r = DBManager::Get()->exec("UPDATE `config` SET `field` = 'PRIVACY_URL', `section` = 'privacy' WHERE `config`.`field` = 'DATENSCHUTZ_URL';");
        DBManager::Get()->exec("UPDATE `config_values` SET `field` = 'PRIVACY_URL' WHERE `config_values`.`field` = 'DATENSCHUTZ_URL';");

        if (!$r) {
            DBManager::Get()->exec("INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`) VALUES ('PRIVACY_URL', '', 'string', 'global', 'privacy', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'URL zur Datenschutzerklärung')");

            DBManager::Get()->exec("INSERT INTO `siteinfo_details` (`detail_id`, `rubric_id`, `position`, `name`, `content`) VALUES (NULL, '1', NULL, 'Datenschutzerklärung',
            '++**Datenschutzerklärung**++

Sie erhalten als Nutzer/-in unserer Internetseite in dieser Datenschutzerklärung notwendige Informationen darüber, wie und in welchem Umfang sowie zu welchem Zweck die  **[Betreibereinrichtung]** Daten von Ihnen erhebt und wie diese verwendet werden. Die Daten werden nur innerhalb der **[Betreibereinrichtung]** verarbeitet und verwendet und nicht an Dritte weitergegeben.


++**Rechtsgrundlagen**++

Die Erhebung und Nutzung Ihrer Daten erfolgt streng nach den gesetzlichen Vorgaben. Regelungen dazu finden sich in:
Europäische Datenschutzgrundverordnung (EU DSGVO)
Bundesdatenschutzgesetz (BDSG)
Niedersächsisches Datenschutzgesetz (NDSG)
Teledienstegesetz (TDG)
Mediendienste-Staatsvertrag (MDStV)
Teledienstedatenschutzgesetz (TDDSG).


++**Personenbezogene Daten**++

Personenbezogene Daten werden zum Zwecke der administrativen Nutzerverwaltung, zur Kontaktaufnahme und Interaktion mit Ihnen sowie zur Bereitstellung personalisierter Dienste [zur Durchführung Ihres Studium bzw. Ihrer Arbeit an **[Betreibereinrichtung]**] von uns gespeichert.
Für die Nutzung von Stud.IP  werden folgende Daten abgefragt und gespeichert:
- Nutzername
- Vorname, Nachname
- Mailadresse
- [ggf. weitere Daten]


Weitere Daten, die evtl. Ihnen gespeichert werden, sind Inhalte, die Sie selbst im Rahmen Ihrer Arbeit oder Ihres Studiums in Stud.IP einstellen. Dazu gehören:
- Freiwillige Angaben zur Person
- Beiträge in Foren
- hochgeladene Dateien
- Chatverläufe in Blubber
- interne Nachrichten
- Kalendereinträge und Stundenpläne
- Teilnahme an Lehrveranstaltungen, Studiengruppen, Orgagremien
- Persönliche Einstellungen und Konfigurationen
- [ggf. Plugindaten]


Diese Inhalte werden mit Ihrem Klarnamen gespeichert und angezeigt. Sie haben die Möglichkeit über die Privatsphäreeinstellungen selbst zu bestimmen, ob und ggf. welche Personengruppen diese Daten sehen dürfen. Diese Daten werden von Stud.IP intern verschlüsselt abgelegt.


++**Aufbewahrungsfristen **++

Ihre personenbezogenen Daten werden für die Dauer Ihres Studiums/Ihrer Arbeit bei [Beitreibereinrichtung] gespeichert. Nach Beendigung ihrer Tätigkeit und Ablauf der gesetzlichen Aufbewahrungsfristen werden Ihre Daten gelöscht.


++**Auskunft, Löschung, Sperrung**++

Sie erhalten jederzeit auf Anfrage Auskunft über die von uns über Sie gespeicherten personenbezogenen Daten sowie dem Zweck von Datenerhebung sowie Datenverarbeitung. Bitte wenden Sie sich hierzu an o.g. Kontaktadresse.

Außerdem haben Sie das Recht, die Berichtigung, die Sperrung oder Löschung Ihrer Daten zu verlangen. Sie können Ihre Einwilligung ohne Angabe von Gründen durch Schreiben an die o.g. Kontakadresse widerrufen. Ihre Daten werden dann umgehend gelöscht. Eine weitere Nutzung der Lernplattform Stud.IP ist dann aber nicht mehr möglich.

Ausgenommen von der Löschung sind Daten, die aufgrund gesetzlicher Vorschriften aufbewahrt oder zur ordnungsgemäßen Geschäftsabwicklung benötigt werden. Damit eine Datensperre jederzeit realisiert werden kann, werden Daten zu Kontrollzwecken in einer Sperrdatei vorgehalten.

Werden Daten nicht von einer gesetzlichen Archivierungspflicht erfasst, löschen wir Ihre Daten auf Ihren Wunsch. Greift die Archivierungspflicht, sperren wir Ihre Daten. Für alle Fragen und Anliegen zur Berichtigung, Sperrung oder Löschung von personenbezogenen Daten wenden Sie sich bitte an unsere Datenschutzbeauftragten unter den Kontaktdaten in dieser Datenschutzerklärung bzw. an die im Impressum genannte Adresse.


++**Datenübertragbarkeit**++

Sie haben das Recht, jederzeit Ihre Daten ausgehändigt zu bekommen. Auf Anfrage stellen wir Ihnen Ihre Daten in menschenlesbaren, gängigen und bearbeitbaren Formaten zur Verfügung.


++**Cookies**++

Stud.IP verwendet ein Session-Cookie. Diese kleine Textdatei beinhaltet lediglich eine verschlüsselte Zeichenfolge, die bei der Navigation im System hilft. Das Cookie wird bei der Abmeldung aus Stud.IP oder beim Schließen des Browsers gelöscht.


++**Server Logfiles**++

Mit dem Zugriff auf Stud.IP werden IP-Adresse, Datum, Uhrzeit und Browserversion zum Zeitpunkt des Zugriffs registriert und anonymisiert gespeichert. Die Erhebung und Nutzung dieser Log-File-Daten dient lediglich der Auswertung zu rein statistischen Forschungs- und Evaluationszwecken der Lernplattform, werden also nicht in Verbindung mit Namen oder Mailadresse gespeichert oder ausgewertet. Diese Daten werden für die Zeit von [X] Monaten auf gesicherten Systemen der **[Betreibereinrichtung]** gespeichert und ebenfalls nicht an Dritte weitergegeben.


++**SSL-Verschlüsselung**++

Die Verbindung zu Stud.IP erfolgt mit einer SSL-Verschlüsselung. Über SSL verschlüsselte Daten sind nicht von Dritten lesbar. Übermitteln Sie Ihre vertraulichen Informationen nur bei aktivierter SSL-Verschlüsselung und wenden Sie sich im Zweifel an uns.


++Kontaktdaten:++
**Name:**
**Telefonnummer:**
**E-Mail-Adresse:**
**Unternehmensbezeichnung:**

++Datenschutzbeauftragte/-r:++
**Name:**
**Telefonnummer:**
**E-Mail-Adresse:**
**Unternehmensbezeichnung:**


')");

        $query = "SELECT `rubric_id`, `detail_id`
                  FROM `siteinfo_details`
                  WHERE `name` = 'Datenschutzerklärung'
                  ORDER BY `detail_id` DESC";
        $result = DBManager::get()->fetchOne($query);
        $datenschutzinfo_url = "dispatch.php/siteinfo/show/{$result['rubric_id']}/{$result['detail_id']}";
        DBManager::Get()->execute("INSERT INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`) VALUES ('PRIVACY_URL', 'studip', ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'URL zur Datenschutzerklärung')", [$datenschutzinfo_url]);

        }

    }

    public function down()
    {
        $query = "DELETE FROM `config`
                  WHERE `field` IN ('PRIVACY_PERM', 'PRIVACY_URL', 'PRIVACY_CONTACT')";
        DBManager::get()->exec($query);
    }

}
