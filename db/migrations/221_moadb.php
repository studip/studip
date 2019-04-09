<?php
/**
 * @author  André Noack <noack@data-quest.de>
 * @license GPL2 or any later version
 *
*/

class Moadb extends Migration
{
    public function description()
    {
        return 'migrates documents to moadb';
    }

    private function insert52aTermsOfUse($db)
    {
        // Copied and modified from cli script add_52a_tables from Stud.IP 3.5:
        $db->exec("INSERT INTO `content_terms_of_use_entries` (`id`, `name`, `position`, `description`, `student_description`, `download_condition`, `icon`, `is_default`, `mkdate`, `chdate`) VALUES
('3RD_PARTY_FALSE', 'Dokument ist frei von Rechten Dritter', 6, '', '', 0, 'check-circle', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('3RD_PARTY_TRUE', 'Dokument ist nicht frei von Rechten Dritter', 7, '', '', 0, 'decline-circle', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('FREE_LICENSE', 'Werk mit freier Lizenz', 3, 'Werke, die unter einer freien Lizenz veröffentlich wurden, d.h. deren Weitergabe und zumeist auch Veränderung ohne Lizenzkosten gestattet ist, dürfen Sie ohne Einschränkungen für den Unterricht zugänglich machen. \n\nTypische Beispiele sind:\n- Open-Access-Publikationen \n- Open Educational Ressources (OER) \n- Werke unter Creative-Commons-Lizenzen (z.B. Wikipedia-Inhalte) \n\nAchtung: Vergewissern Sie sich im Einzelfall, welche Einschränkungen für die Verbreitung und Veränderung die jeweilige Lizenz ggf. enthält.', 'Das Dokument unterliegt einer freien Lizenz. Sie dürfen es weitergeben und unter Beachtung der Details der Lizenz (s. Angaben im Dokument) verändern und in eigene Werke übernehmen.', 0, 'cc', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('SELFMADE_NONPUB', 'Selbst verfasstes, nicht publiziertes Werk', 2, 'Selbst verfasste Werke dürfen Sie ohne Einschränkungen zugänglich machen, wenn Sie die Verwertungsrechte nicht an einen Verlag abgetreten haben. \nTypische Beispiele sind selbst verfasste:\n - Präsentationsfolien, auch mit Text- und Bildzitaten aus fremden Quellen \n- Übungsaufgaben, Musterlösungen \n- Computer-Programme \n- Literaturlisten, Seminarpläne\n - Vorlesungsskripte \n\nWichtig ist die Beachtung des Zitatrechtes: \nWenn Sie Teile fremder Quellen übernehmen, ist das zulässig, solange diese Teile mit Quelle gekennzeicht werden und Gegenstand einer wissenschaftlichen Auseinandersetzung sind.', 'Das Dokument wird von den Autor/-innen zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben. Für darüber hinaus gehende Erlaubnisse (Weitergabe, Veränderung) wenden Sie sich an die Autor/-innen oder beachten Sie die Hinweise im Dokument.', 0, 'own-license', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('NO_LICENSE', 'Veröffentlichte Werke ohne erworbene Lizenz oder gesonderte Erlaubnis', 5, 'Veröffentlichte Werke, für die keine Lizenz erworben wurde und für die keine gesonderte Erlaubnis vorliegt, dürfen unter den Erlaubnissen des § 60a UrhG für Unterrichtsteilnehmende zugänglich gemacht werden.\n\nEs muss sich dabei um kleine Teile des Gesamtwerkes handeln (z.B. max.  15% eines Buches oder Bildbandes, 5 Minuten bei Musikstücken oder Filmen, Kinofilme erst nach 2 Jahren). Einzelne Abbildungen, Photos oder Artikel aus wissenschaftlichen Zeitschriften dürfen ganz zugänglich gemacht werden, Artikel aus Zeitungen und anderen Zeitschriften allerdings ebenfalls nur zu 10%.\n\nZum Hintergrund: Diese Regelung gilt wegen der Befristung des § 60a UrhG zunächst bis März 2023, eine Einzelmeldung oder Abrechnung über die Hochschule o.ä. ist nicht erforderlich.', 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', 0, '60a', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('UNDEF_LICENSE', 'Ungeklärte Lizenz', 1, 'Bitte geben Sie an, welcher Lizenz das hochgeladene Material unterliegt bzw. auf welcher Grundlage Sie es zugänglich machen. Unterbleibt diese Angabe, wird beim Herunterladen auf den ungeklärten Lizenzstatus hingewiesen.', 'Diese Datei enthält Material mit einer ungeklärten Lizenz. Zu Fragen der Nutzung und Weitergabe wenden Sie sich an die Person, die diese Datei hochgeladen hat.', 2, 'question-circle', 1, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('WITH_LICENSE', 'Nutzungserlaubnis oder Lizenz liegt vor', 4, 'Wenn Sie urheberrechtlich geschützte Werke zugänglich machen wollen und keine der anderen Kategorien passt, benötigen Sie eine Erlaubnis oder kostenpflichtige Lizenz des Inhabers der Verwertungsrechte. Das ist bei publizierten Werken der Verlag, bei nicht publizierten Werken der Autor. \n\nTypische Beispiele sind: \n- Zustimmung von Kollegen oder Studierenden zur Weitergabe von Skripten, Seminararbeiten, Referatsfolien \n- Zustimmung eines Verlages zur Nutzung von Werkteilen für die Lehre \n- Verlags-Erlaubnis zur Nutzung eigener publizierter Werke für die Lehre \n- Erworbene Lizenz für die Weitergabe in Lehrveranstaltung (eine einzelne erworbene Kopie reicht nicht aus!) \n\nAchtung: Campus- oder Nationallizenzen erlauben es nicht, dass Sie ein Werk erneut hochladen und somit selbst verbreiten. Verlinken Sie in diesem Fall direkt auf das Angebot Ihrer Bibliothek o.ä.', 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', 0, 'license', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");

    }

    private function updateLicenseIds($db)
    {
        //We must convert the old IDs from the document_licenses table
        //to the new IDs from the content_terms_of_use_entries table:
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_FALSE' WHERE content_terms_of_use_id = '0'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_TRUE' WHERE content_terms_of_use_id = '1'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'UNDEF_LICENSE' WHERE content_terms_of_use_id = '2'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'SELFMADE_NONPUB' WHERE content_terms_of_use_id = '3'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'FREE_LICENSE' WHERE content_terms_of_use_id = '4'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'WITH_LICENSE' WHERE content_terms_of_use_id = '5'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'NO_LICENSE' WHERE content_terms_of_use_id = '6'");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'NO_LICENSE' WHERE content_terms_of_use_id = '7'");
    }

    private function insertFilesTour($db)
    {
        $db->exec("INSERT INTO `help_tours` (`global_tour_id`, `tour_id`, `name`, `description`, `type`, `roles`, `version`, `language`, `studip_version`, `installation_id`, `author_email`, `mkdate`, `chdate`) VALUES
('e9959c638e0c2578cccee24702e886f4', '0b542c6c891af499763356f2c7218f7f', 'Was ist neu in Stud.IP 4.0?', 'Was ist neu in Stud.IP 4.0?', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '4.0', '', '', 1514883131, 0)");

        $db->exec("INSERT INTO `help_tour_settings` (`tour_id`, `active`, `access`) VALUES
('0b542c6c891af499763356f2c7218f7f', 1, 'autostart_once')");

        $db->exec("INSERT INTO `help_tour_steps` (`tour_id`, `step`, `title`, `tip`, `orientation`, `interactive`, `css_selector`, `route`, `action_prev`, `action_next`, `author_email`, `mkdate`, `chdate`) VALUES
('0b542c6c891af499763356f2c7218f7f', 1, 'Willkommen in Stud.IP 4!', 'Unter der Haube ist alles neu, auch an der Oberfläche hat sich einiges getan.', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1514883131, 1514883295),
('0b542c6c891af499763356f2c7218f7f', 2, '', 'Die wichtigsten Neuigkeiten im Schnelldurchlauf:', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1514883334, 1514883383),
('0b542c6c891af499763356f2c7218f7f', 3, '', 'Die Startseite lässt sich anpassen. Sie können selbst bestimmen, was angezeigt werden soll. Per Drag & Drop können sie die Position der Elemente auf der Startseite verändern.', 'R', 0, '.sidebar-widget:eq(1) A:eq(0)', 'dispatch.php/start', '', '', 'root@localhost', 1514883313, 1516748869),
('0b542c6c891af499763356f2c7218f7f', 4, '', 'Alle persönlichen Funktionen sind in diesem Menü zusammengefasst. Der persönliche Dateibereich ist nun immer standardmäßig eingeschaltet.', 'B', 0, '#avatar-arrow', 'dispatch.php/start', '', '', 'root@localhost', 1514883360, 1516749166),
('0b542c6c891af499763356f2c7218f7f', 5, '', 'Alle Dateibereiche wurden in Stud.IP 4 komplett überarbeitet. Im persönlichen Dateibereich finden sich u.a. Nachrichtenanhänge. Sie können aber auch eigene Ordner erstellen und diese auf ihrer Profilseite anderen zugänglich machen. In Veranstaltungen gibt es weitere Typen von Dateiordnern, wie den Hausaufgabenordner.', 'B', 0, '', 'dispatch.php/files', '', '', 'root@localhost', 1514883588, 1514883588),
('0b542c6c891af499763356f2c7218f7f', 6, '', 'Falls von der Hochschule gestattet, können Sie hier Owncloud/Nextcloud oder Powerfolder für die Dateiverwaltung koppeln.', 'R', 0, '.sidebar-widget:eq(0) A:eq(0)', 'dispatch.php/files', '', '', 'root@localhost', 1514883641, 1516749255),
('0b542c6c891af499763356f2c7218f7f', 7, '', 'Weniger häufig benötigte Funktionen sind in Stud.IP 4 hinter dem Aktionsmenü mit den drei Punkten zu finden.', 'LT', 0, 'table.documents nav.action-menu', 'dispatch.php/files', '', '', 'root@localhost', 1514883706, 1516749543),
('0b542c6c891af499763356f2c7218f7f', 8, '', 'Ebenfalls neu ist die dezente Navigationszeile. Der gerade aktive Bereich wird durch eine Linie angezeigt.', 'B', 0, '#tabs', 'dispatch.php/files', '', '', 'root@localhost', 1514883773, 1514883781),
('0b542c6c891af499763356f2c7218f7f', 9, '', 'Das waren die allerwichtigsten Dinge im Überblick. In Stud.IP 4 hat sich aber noch viel mehr getan. Jedes Detail wurde durchdacht und verbessert . Damit ist Stud.IP 4 das modernste Open-Source-LMS auf dem Markt.', 'B', 0, '', 'dispatch.php/files', '', '', 'root@localhost', 1514883871, 1514883871),
('0b542c6c891af499763356f2c7218f7f', 10, 'Das Stud.IP-Team wünscht viel Erfolg bei der Arbeit mit Stud.IP 4!', '', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1514883886, 1514883946)");
    }

    public function up()
    {
        $db = DBManager::get();

        try {
            $db->exec("RENAME TABLE files TO _files");
            $db->exec("RENAME TABLE file_refs TO _file_refs");
        } catch (PDOException $e)
        {

        }

        $db->exec("CREATE TABLE IF NOT EXISTS `files` (
                 `id` varchar(32) NOT NULL,
                 `user_id` varchar(32) NOT NULL,
                 `mime_type` varchar(255) NOT NULL DEFAULT '',
                 `name` varchar(255)  NOT NULL,
                 `size` int(10) unsigned NOT NULL,
                 `storage` enum('disk','url')  NOT NULL DEFAULT 'disk',
                 `author_name` varchar(100) NOT NULL DEFAULT '',
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_refs` (
                 `id` VARCHAR(32) NOT NULL,
                 `file_id` VARCHAR(32) NOT NULL,
                 `folder_id` VARCHAR(32) NOT NULL,
                 `downloads` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                 `description` TEXT NOT NULL,
                 `content_terms_of_use_id` VARCHAR(32) NOT NULL,
                 `user_id` VARCHAR(32) NOT NULL DEFAULT '',
                 `name` VARCHAR(255) NOT NULL DEFAULT '',
                 `mkdate` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                 `chdate` INT(10) UNSIGNED NOT NULL DEFAULT 0,
                 PRIMARY KEY (`id`)
                 ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_urls` (
                 `file_id` varchar(32)  NOT NULL,
                 `url` varchar(4096)  NOT NULL,
                 `access_type` enum('proxy','redirect') NOT NULL DEFAULT 'proxy',
                 PRIMARY KEY (`file_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `folders` (
                 `id` varchar(32)  NOT NULL,
                 `user_id` varchar(32)  NOT NULL,
                 `parent_id` varchar(32)  NOT NULL,
                 `range_id` varchar(32)  NOT NULL,
                 `range_type` varchar(32)  NOT NULL,
                 `folder_type` varchar(255)  NOT NULL,
                 `name` varchar(255)  NOT NULL,
                 `data_content` text  NOT NULL,
                 `description` text  NOT NULL,
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        //table for SORM class ContentTermsOfUse:
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `content_terms_of_use_entries` (
            `id` VARCHAR(32) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `position` int(10) unsigned NOT NULL,
            `description` TEXT NOT NULL,
            `student_description` TEXT NOT NULL,
            `download_condition` TINYINT(2) NOT NULL,
            `icon` VARCHAR(128) NOT NULL DEFAULT '',
            `is_default` tinyint(2) unsigned NOT NULL DEFAULT 0,
            `mkdate` int(10) unsigned NOT NULL,
            `chdate` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC"
        );


        //default terms of use entries:
        $this->insert52aTermsOfUse($db);


        $db->exec("SET autocommit=0");
        //top folder courses
        $institute_folders = [];
        foreach ($db->query("SELECT DISTINCT i.institut_id as new_range_id,i.name FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.seminar_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'institute', 'RootFolder');
            $institute_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        $db->exec("COMMIT");
        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        //other top folders
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON BINARY MD5(CONCAT(i.institut_id, _latin1'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        unset($institute_folders);
        $db->exec("COMMIT");


        $seminar_folders = [];
        foreach ($db->query("SELECT DISTINCT s.seminar_id as new_range_id,s.name FROM  `seminare` s INNER JOIN `folder` f ON s.Seminar_id = f.seminar_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'course', 'RootFolder');
            $seminar_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        $db->exec("COMMIT");

        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }
        $db->exec("COMMIT");

        //other top folders
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON BINARY MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }
        $db->exec("COMMIT");

        //group folder
        foreach ($db->query("SELECT f.*, s.range_id AS seminar_id FROM `folder` f INNER JOIN `statusgruppen` s ON s.statusgruppe_id = f.range_id") as $folder) {
            $data_content = json_encode(['group' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseGroupFolder', $data_content);
        }
        //issue folder
        foreach ($db->query("SELECT f.*, t.seminar_id AS seminar_id FROM `folder` f INNER JOIN `themen` t ON t.issue_id = f.range_id") as $folder) {
            $data_content = json_encode(['topic_id' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseTopicFolder', $data_content);
        }
        $db->exec("COMMIT");

        //personal documents
        $insert_personal_folder = $db->prepare("INSERT IGNORE INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, 'user', ?, ?, ?, ?, ?, ?)");
        foreach ($db->fetchFirst("SELECT distinct parent_id FROM `_file_refs` inner join auth_user_md5 where parent_id=user_id") as $user_id) {
            $top_folder_id = $db->fetchColumn("SELECT id FROM folders WHERE range_type = 'user' AND parent_id='' AND range_id=?", [$user_id]);
            if (!$top_folder_id) {
                $top_folder_id = md5(uniqid($user_id));
                $insert_personal_folder->execute([
                    $top_folder_id,
                    $user_id,
                    '',
                    $user_id,
                    'RootFolder',
                    '',
                    '',
                    '',
                    time(),
                    time()
                ]);
            }
            $personal_folder_id = md5($top_folder_id . 'personal_top_folder_id');
            $insert_personal_folder->execute([
                $personal_folder_id,
                $user_id,
                $top_folder_id,
                $user_id,
                'PublicFolder',
                'öffentliche Dateien',
                Config::get()->PERSONALDOCUMENT_OPEN_ACCESS ? '{"viewable":1}' : '',
                '',
                time(),
                time()
            ]);
            $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", [$user_id]), $personal_folder_id);
            $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,'{$personal_folder_id}' as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", [$user_id]);
            foreach ($subfolders as $one) {
                $this->migratePersonalFolder($one, $user_id);
            }
        }
        $db->exec("COMMIT");

        //Blubber folders
        foreach ($db->query("SELECT f.*, a.user_id AS seminar_id, CONCAT_WS(' ', vorname,nachname) as name
                            FROM `folder` f
                            INNER JOIN `auth_user_md5` a ON a.user_id = f.range_id") as $folder) {
            $user_id = $folder['seminar_id'];
            $top_folder_id = $db->fetchColumn("SELECT id FROM folders WHERE range_type = 'user' AND parent_id='' AND range_id=?", [$user_id]);
            if (!$top_folder_id) {
                $top_folder_id = md5(uniqid($user_id));
                $insert_personal_folder->execute([
                    $top_folder_id,
                    $user_id,
                    '',
                    $user_id,
                    'RootFolder',
                    '',
                    '',
                    '',
                    time(),
                    time()
                ]);
            }
            $personal_folder_id = md5($top_folder_id . 'personal_top_folder_id');
            $insert_personal_folder->execute([
                $personal_folder_id,
                $user_id,
                $top_folder_id,
                $user_id,
                'PublicFolder',
                'öffentliche Dateien',
                Config::get()->PERSONALDOCUMENT_OPEN_ACCESS ? '{"viewable":1}' : '',
                '',
                time(),
                time()
            ]);

            $folder['range_id'] = $personal_folder_id;
            $folder['description'] = '';
            $this->migrateFolder($folder, $user_id, 'user', 'PublicFolder');
        }

        $db->exec("COMMIT");

        //migrate message attachments:
        $this->migrateMessageAttachments();


        //map old 52 license IDs to new terms of use entries:
        $this->updateLicenseIds($db);
        $db->exec("COMMIT");
        $db->exec("SET autocommit=1");

        $db->exec("ALTER TABLE `file_refs`
                  ADD KEY `file_id` (`file_id`),
                  ADD KEY `folder_id` (`folder_id`)");
        $db->exec("ALTER TABLE `folders`
                  ADD KEY `range_id` (`range_id`),
                  ADD KEY `parent_id` (`parent_id`)");

        //delete configuration variables designed for the old file area:

        $db->exec(
            "DELETE FROM `config`
            WHERE
            `field` IN
            ('PERSONALDOCUMENT_OPEN_ACCESS',
            'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED',
            'PERSONALDOCUMENT_ENABLE',
            'FILESYSTEM_MULTICOPY_ENABLE',
            'DOCUMENTS_EMBEDD_FLASH_MOVIES',
            'ALLOW_DOWNLOAD_FOR_UNKNOWN_LICENSE',
            'COPYRIGHT_DIALOG_ON_UPLOAD',
            'LICENSE_PREAMBLE',
            'DEFAULT_LICENSE_ON_UPLOAD'
            )"
        );
        $db->exec("DROP TABLE IF EXISTS `doc_filetype`, `doc_filetype_forbidden`, `doc_usergroup_config`, `dokumente`, `files_backend_studip`, `files_backend_url`, `files_share`, `folder`, `_files`, `_file_refs`, `document_licenses`");

        //add help tour
        $this->insertFilesTour($db);
    }




    public function migrateMessageAttachments()
    {
        //First we wipe out all documents with range-ID = 'provisional'.
        //Such documents were meant to be attached to mails but were left
        //unattached... to remain lonely in the database...
        //So it's time to end this misery and delete them!
        /*
        $unattached_documents = StudipDocument::deleteBySql(
            "range_id = 'provisional'"
        );
        */

        $db = DBManager::get();

        //then we retrieve all message-IDs:
        $message_rows = $db->query("SELECT DISTINCT message_id,autor_id,subject,message.mkdate FROM message INNER JOIN dokumente ON range_id = message_id");
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach($message_rows as $message_row) {
            //now we loop through each message ID and check if there are
            //files in the dokumente table with that range-ID:
            $message_id = $message_row['message_id'];

                //we found at least one attachment: create a top folder for this message:


                $folder_id = md5($message_id . '_attachments');

                $insert_folder->execute([
                    $folder_id,
                    $message_row['autor_id'],
                    '',
                    $message_id,
                    'message',
                    'MessageFolder',
                    $message_row['subject'],
                    '',
                    '',
                    $message_row['mkdate'],
                    $message_row['mkdate']
                ]);

                $this->migrateFiles($db->fetchAll(
                    "SELECT * FROM dokumente WHERE range_id = :range_id",
                    [
                        'range_id' => $message_id
                    ]
                ), $folder_id);

        }
    }


    public function migrateFolder($folder, $range_id, $range_type, $folder_type, $data_content = '')
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $folder_type = $folder_type == 'StandardFolder' && isset($folder['permission']) && $folder['permission'] != 7 ? 'PermissionEnabledFolder' : $folder_type;
        $data_content = $data_content == '' && isset($folder['permission']) && $folder['permission'] != 7 ? json_encode(['permission' => $folder['permission']]): $data_content;
        if (isset($folder['range_id'])) {
            $insert_folder->execute([$folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $range_type, $folder_type, $folder['name'], $data_content, (string)$folder['description'], $folder['mkdate'], $folder['chdate']]);
        }
        $subfolders = $db->fetchAll("SELECT * FROM folder WHERE range_id = ?", [$folder['folder_id']]);
        foreach ($subfolders as $one) {
            $this->migrateFolder($one, $range_id, $range_type, 'StandardFolder');
        }
        $this->migrateFiles($db->fetchAll("SELECT * FROM dokumente WHERE range_id = ?", [$folder['folder_id']]), $folder['folder_id']);



    }

    public function migrateFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file_url = $db->prepare("INSERT INTO `file_urls` (`file_id`, `url`) VALUES (?, ?)");
        $filenames = [];
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute([
                $one['dokument_id'],
                $one['dokument_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                $one['protected'],
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ]);
            $insert_file->execute([$one['dokument_id'], $one['user_id'], get_mime_type($one['filename']), $filename, $one['filesize'], $one['url'] ? 'url' : 'disk', $one['author_name'], $one['mkdate'], $one['chdate']]);
            if ($one['url']) {
                $insert_file_url->execute([$one['dokument_id'], $one['url']]);
            }
        }
    }

    public function migratePersonalFolder($folder, $range_id)
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, 'user', 'PublicFolder', ?, ?, ?, ?, ?)");

        $insert_folder->execute([
            $folder['folder_id'],
            $folder['user_id'],
            $folder['range_id'],
            $range_id,
            $folder['name'],
            Config::get()->PERSONALDOCUMENT_OPEN_ACCESS ? '{"viewable":1}' : '',
            (string)$folder['description'],
            $folder['mkdate'],
            $folder['chdate']
        ]);
        $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,parent_id as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", [$folder['folder_id']]);
        foreach ($subfolders as $one) {
            $this->migratePersonalFolder($one, $range_id);
        }
        $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", [$folder['folder_id']]), $folder['folder_id']);


    }

    public function migratePersonalFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $filenames = [];
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute([
                $one['id'],
                $one['storage_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                0,
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ]);
            $insert_file->execute([$one['storage_id'], $one['user_id'], $one['mime_type'], $filename, $one['size'], 'disk', '', $one['mkdate'], $one['chdate']]);
            $new_path = $GLOBALS['UPLOAD_PATH'] . '/' . substr($one['storage_id'], 0, 2) . '/' . $one['storage_id'];
            $old_path = $GLOBALS['USER_DOC_PATH'] . '/' . $one['user_id'] . '/' . $one['storage_id'];
            @rename($old_path, $new_path);
        }
    }

    public function down()
    {
        /*
         * I'M SORRY DAVE, I'M AFRAID I CAN'T DO THAT
         */
    }
}
