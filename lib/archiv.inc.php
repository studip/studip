<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/*

archiv.inc.php - Funktionen zur Archivierung in Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require_once 'lib/dates.inc.php';
require_once 'lib/wiki.inc.php'; // getAllWikiPages for dump
require_once 'lib/user_visible.inc.php';

/**
 * This function returns the last activity in the course.
 *
 * @param  string $sem_id the id of the course
 * @return int timestamp of last activity (max chdate)
 */
function lastActivity ($sem_id)
{
    // Cache query generation
    static $query = null;
    if ($query === null) {
        $queries = [
            // Veranstaltungs-data
            "SELECT chdate FROM seminare WHERE Seminar_id = :id",
            // Folders
            "SELECT MAX(chdate) AS chdate FROM folders WHERE range_id = :id",
            // Documents
            "SELECT MAX(file_refs.chdate) AS chdate FROM file_refs
                INNER JOIN folders
                ON file_refs.folder_id = folders.id
                WHERE folders.range_id = :id",
            // SCM
            "SELECT MAX(chdate) AS chdate FROM scm WHERE range_id = :id",
            // Dates
            "SELECT MAX(chdate) AS chdate FROM termine WHERE range_id = :id",
            // News
            "SELECT MAX(`date`) AS chdate FROM news_range LEFT JOIN news USING (news_id) WHERE range_id = :id",
            // Literature
            "SELECT MAX(chdate) AS chdate FROM lit_list WHERE range_id = :id",
        ];

        // Votes
        if (get_config('VOTE_ENABLE')) {
            $queries[] = "SELECT MAX(questionnaires.chdate) AS chdate FROM questionnaires INNER JOIN questionnaire_assignments ON (questionnaire_assignments.questionnaire_id = questionnaires.questionnaire_id) WHERE questionnaire_assignments.range_id = :id";
        }

        // Wiki
        if (get_config('WIKI_ENABLE')) {
            $queries[] = "SELECT MAX(chdate) AS chdate FROM wiki WHERE range_id = :id";
        }

        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $table = $plugin->getEntryTableInfo();
            $queries[] = 'SELECT MAX(`'. $table['chdate'] .'`) AS chdate FROM `'. $table['table'] .'` WHERE `'. $table['seminar_id'] .'` = :id';
        }

        $query = "SELECT MAX(chdate) FROM (" . implode(' UNION ', $queries) . ") AS tmp";
    }

    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':id', $sem_id);
    $statement->execute();
    $timestamp = $statement->fetchColumn() ?: 0;

    //correct the timestamp, if date in the future (news can be in the future!)
    if ($timestamp > time()) {
        $timestamp = time();
    }

    return $timestamp;
}

// Liefert den dump des Seminars
function dump_sem($sem_id, $print_view = false)
{
    global $TERMIN_TYP, $SEM_TYPE, $SEM_CLASS, $_fullname_sql, $AUTO_INSERT_SEM;

    $Modules = new Modules;
    $Modules = $Modules->getLocalModules($sem_id);

    $query = "SELECT status, Name, Untertitel, art, VeranstaltungsNummer,
                     ects, Beschreibung, teilnehmer, vorrausetzungen,
                     lernorga, leistungsnachweis, Sonstiges, Institut_id,
                     admission_turnout
              FROM seminare
              WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $seminar = $statement->fetch(PDO::FETCH_ASSOC);

    $sem_type = $seminar['status'];

    $sem = Seminar::getInstance($sem_id);

    $dump  = '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
    $dump .= '<tr><td colspan="2" align="left" class="table_header_bold">';
    $dump .= '<h1 class="table_header_bold">&nbsp;' . htmlReady($seminar['Name'], 1, 1) . '</h1>';
    $dump .= '</td></tr>' . "\n";

    // Helper function that dumps into a single table row
    $dumpRow = function ($title, $content, $escape = false) use (&$dump) {
        $content = trim($content);
        if ($content) {
            if ($escape) {
                $content = htmlReady($content, 1, 1);
            }
            $dump .= sprintf('<tr><td width="15%%"><b>%s</b></td><td>%s</td></tr>' . "\n",
                             htmlReady($title), $content);
        }
    };

    //Grunddaten des Seminars, wie in den seminar_main
    $dumpRow(_('Untertitel:'), $seminar['Untertitel'], true);

    if ($data = $sem->getDatesExport()) {
        $dumpRow(_('Zeit:'), nl2br($data));
    }

    $dumpRow(_('Semester:'), get_semester($sem_id));
    $dumpRow(_('Erster Termin:'), veranstaltung_beginn($sem_id, 'export'));

    if ($temp = vorbesprechung($sem_id, 'export')) {
        $dumpRow(_('Vorbesprechung:'), htmlReady($temp));
    }

    if ($data = $sem->getDatesTemplate('dates/seminar_export_location')) {
        $dumpRow(_('Ort:'), nl2br($data));
    }

    //wer macht den Dozenten?
    $query = "SELECT {$_fullname_sql['full']} AS fullname
              FROM seminar_user
              LEFT JOIN auth_user_md5 USING (user_id)
              LEFT JOIN user_info USING (user_id)
              WHERE Seminar_id = ? AND status = 'dozent'
              ORDER BY position, Nachname, Vorname";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $teachers = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (count($teachers) > 0) {
        $title = get_title_for_status('dozent', count($teachers), $sem_type);
        $dumpRow($title, implode('<br>', array_map('htmlReady', $teachers)));
    }

    //und wer ist Tutor?
    $query = "SELECT {$_fullname_sql['full']} AS fullname
              FROM seminar_user
              LEFT JOIN auth_user_md5 USING (user_id)
              LEFT JOIN user_info USING (user_id)
              WHERE Seminar_id = ? AND status = 'tutor'
              ORDER BY position, Nachname, Vorname";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $tutors = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (count($tutors) > 0) {
        $title = get_title_for_status('tutor', count($tutors), $sem_type);
        $dumpRow($title, implode('<br>', array_map('htmlReady', $tutors)));
    }

    if ($seminar['status'] != '' && isset($SEM_TYPE[$seminar['status']])) {
        $content  = $SEM_TYPE[$seminar['status']]['name'];
        $content .= ' ' . _('in der Kategorie') . ' ';
        $content .= '<b>' . $SEM_CLASS[$SEM_TYPE[$seminar['status']]['class']]['name'] . '</b>';
        $dumpRow(_('Typ der Veranstaltung'), $content);
    }

    $dumpRow(_('Art der Veranstaltung:'), $seminar['art'], true);
    $dumpRow(_('VeranstaltungsNummer:'), htmlReady($seminar['VeranstaltungsNummer']));
    $dumpRow(_('ECTS-Punkte:'), htmlReady($seminar['ects']));
    $dumpRow(_('Beschreibung:'), $seminar['Beschreibung'], true);
    $dumpRow(_('Teilnehmende:'), $seminar['teilnehmende'], true);
    $dumpRow(_('Voraussetzungen:'), $seminar['vorrausetzungen'], true);
    $dumpRow(_('Lernorganisation:'), $seminar['lernorga'], true);
    $dumpRow(_('Leistungsnachweis:'), $seminar['leistungsnachweis'], true);

    //add the free adminstrable datafields
    $localEntries = DataFieldEntry::getDataFieldEntries($sem_id);
    foreach ($localEntries as $entry) {
        $dumpRow($entry->getName(), $entry->getDisplayValue());
    }

    $dumpRow(_('Sonstiges:'), $seminar['Sonstiges'], true);

    // Fakultaeten...
    $query = "SELECT DISTINCT c.Name
              FROM seminar_inst AS a
              LEFT JOIN Institute AS b USING (Institut_id)
              LEFT JOIN Institute AS c ON (c.Institut_id = b.fakultaets_id)
              WHERE a.seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $faculties = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (count($faculties) > 0) {
        $dumpRow(_('Fakultät(en):'), implode('<br>', array_map('htmlReady', $faculties)));
    }

    //Studienbereiche
    if (isset($SEM_TYPE[$seminar['status']]) && $SEM_CLASS[$SEM_TYPE[$seminar['status']]['class']]['bereiche']) {
        $sem_path = get_sem_tree_path($sem_id) ?: [];
        $dumpRow(_('Studienbereiche') . ':', implode('<br>', array_map('htmlReady', $sem_path)));
    }

    $iid = $seminar['Institut_id'];
    $query = "SELECT Name FROM Institute WHERE Institut_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$iid]);
    $inst_name = $statement->fetchColumn();
    $dumpRow(_('Heimat-Einrichtung:'), $inst_name, true);

    $query = "SELECT Name
              FROM seminar_inst
              LEFT JOIN Institute USING (institut_id)
              WHERE seminar_id = ? AND Institute.institut_id != ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id, $iid]);
    $other_institutes = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (count($other_institutes) > 0) {
        $title = (count($other_institutes) == 1)
               ? _('Beteiligte Einrichtung:')
               : _('Beteiligte Einrichtungen:');
        $dumpRow($title, implode(', ', array_map('htmlReady', $other_institutes)));
    }

    //Teilnehmeranzahl
    $dumpRow(_('max. Personenanzahl:'), $seminar['admission_turnout']);

    //Statistikfunktionen
    $query = "SELECT COUNT(*) FROM seminar_user WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $count = $statement->fetchColumn();
    $dumpRow(_('Anzahl der angemeldeten Personen:'), $count);

    // number of postings for all forum-modules in this seminar
    $count = 0;
    $forum_modules = PluginEngine::getPlugins('ForumModule', $sem_id);
    foreach ($forum_modules as $plugin) {
        $count += $plugin->getNumberOfPostingsForSeminar($sem_id);
    }
    $dumpRow(_('Forenbeiträge:'), $count);

    $num_files = 0;
    $course_top_folder = Folder::findTopFolder($sem_id);
    if ($course_top_folder) {
        $course_top_folder = $course_top_folder->getTypedFolder();
    }

    $user_id = $print_view === true ? $GLOBALS['user']->id : $print_view;

    $readable_files_user_id = $user_id;

    if ($Modules['documents']) {
        //Get the amount of readable files for a user with status autor in the course:

        $autor = CourseMember::findOneBySql(
            "seminar_id = :course_id AND status = 'autor'",
            [
                'course_id' => $sem_id
            ]
        );
        if ($autor) {
            $readable_files_user_id = $autor->user_id;
            $num_files = FileManager::countFilesInFolder($course_top_folder, true, null, $autor->user_id);
        } else {
            $dozent = CourseMember::findOneBySql(
                "seminar_id = :course_id AND status = 'dozent'",
                [
                    'course_id' => $sem_id
                ]
            );
            $readable_files_user_id = $dozent->user_id;
            $num_files = FileManager::countFilesInFolder($course_top_folder, true, null, $dozent->user_id);
        }
    }

    $dumpRow(_('Dokumente:'), $num_files ? $num_files : 0);

    $dump.= '</table>' . "\n";

    // Ablaufplan
    if ($Modules['schedule']) {
        $dump.= dumpRegularDatesSchedule($sem_id);
        $dump.= dumpExtraDatesSchedule($sem_id);
    }

    //SCM
    if ($Modules['scm']) {
        foreach(StudipScmEntry::findByRange_id($sem_id, 'ORDER BY position ASC') as $scm) {
            if (!empty($scm->content)) {
                $dump .= '<br>';
                $dump .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
                $dump .= ' <tr><td align="left" class="table_header_bold">';
                $dump .= '<h2 class="table_header_bold">&nbsp;' . htmlReady($scm->tab_name) . '</h2>';
                $dump .= '</td></tr>' . "\n";
                $dump .= '<tr><td align="left" width="100%"><br>'. formatReady($scm->content, 1, 1) .'<br></td></tr>' . "\n";
                $dump .= '</table>' . "\n";
            }
        }
    }

    if ($Modules['literature']) {
        $lit = StudipLitList::GetFormattedListsByRange($sem_id, false, false);
        if ($lit) {
            $dump .= '<br>';
            $dump .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
            $dump .= '<tr><td align="left" class="table_header_bold">';
            $dump .= '<h2 class="table_header_bold">&nbsp;' . _('Literaturlisten') . '</h2>';
            $dump .= '</td></tr>' . "\n";
            $dump .= '<tr><td align="left" width="100%"><br>'. $lit .'<br></td></tr>' . "\n";
            $dump .= '</table>' . "\n";
        }
    }

    // Dateien anzeigen
    if ($Modules['documents']) {

        if ($course_top_folder) {
            list($file_refs, $folders) = array_values(
                FileManager::getFolderFilesRecursive(
                    $course_top_folder,
                    $readable_files_user_id,
                    true
                )
            );

            $link_text = _('Hinweis: Diese Datei wurde nicht archiviert, da sie lediglich verlinkt wurde.');


            if ($file_refs) {
                $dump .= '<br>';
                $dump .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
                $dump .= '<tr><td align="left" colspan="3" class="table_header_bold">';
                $dump .= '<h2 class="table_header_bold">&nbsp;' . _('Dateien:') . '</h2>';
                $dump .= '</td></tr>' . "\n";

                foreach ($file_refs as $file_ref) {
                    $dump .= sprintf(
                        '<tr><td width="100%%"><b>%s</b><br>%s (%u KB)</td><td>%s</td><td>%s</td></tr>' . "\n",
                        htmlReady($file_ref->name),
                        htmlReady($file_ref->description),
                        round($file_ref->file->size / 1024),
                        htmlReady($file_ref->owner->nachname),
                        date('d.m.Y', $file_ref->mkdate)
                    );
                }
            }

            $dump .= '</table>' . "\n";
        }
    }

    // Teilnehmer
    if ($Modules['participants']
        && (Config::get()->AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM || !in_array($sem_id, AutoInsert::getAllSeminars(true))))
    {
        $dump .= '<br>';

        $ext_vis_query = get_ext_vis_query('seminar_user');
        $query = "SELECT user_id, {$_fullname_sql['full']} AS fullname,
                         {$ext_vis_query} AS user_is_visible
                    FROM seminar_user
                    LEFT JOIN auth_user_md5 USING (user_id)
                    LEFT JOIN user_info USING (user_id)
                    WHERE Seminar_id = ? AND status = ?
                    GROUP by user_id
                    ORDER BY Nachname, Vorname";
        $user_statement = DBManager::get()->prepare($query);

        foreach (words('dozent tutor autor user') as $key) {
            // die eigentliche Teil-Tabelle

            $user_statement->execute([$sem_id, $key]);
            $users = $user_statement->fetchAll(PDO::FETCH_ASSOC);
            $user_statement->closeCursor();

            //haben wir in der Personengattung ueberhaupt einen Eintrag?
            if (count($users) > 0) {
                $dump .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
                $dump .= '<tr><td align="left" colspan="3" class="table_header_bold">';
                $dump .= '<h2 class="table_header_bold">&nbsp;' . get_title_for_status($key, count($users), $sem_type) . '</h2>';
                $dump .= '</td></tr>' . "\n";
                $dump .= '<th width="30%">' . _('Name') . '</th>';
                $dump .= '<th width="10%">' . _('Forenbeiträge') . '</th>';
                $dump .= '<th width="10%">' . _('Dokumente') . '</th></tr>' . "\n";

                foreach ($users as $user) {
                    //Count the files the user owns in the course:

                    $user_files_count = FileManager::countFilesInFolder(
                        $course_top_folder,
                        true,
                        $user['user_id']
                    );

                    // get number of postings for this user from all forum-modules
                    $postings = 0;
                    foreach ($forum_modules as $plugin) {
                        $postings += $plugin->getNumberOfPostingsForUser($user['user_id'], $sem_id);
                    }

                    $dump .= sprintf(
                        '<tr><td>%s</td><td align="center">%u</td><td align="center">%u</td></tr>' . "\n",
                        $user['user_is_visible']
                        ? htmlReady($user['fullname'])
                        : _('(unsichtbareR NutzerIn)'),
                        $postings,
                        $user_files_count
                    );
                } // eine Zeile zuende

                $dump.= '</table>' . "\n";
            }
        } // eine Gruppe zuende
    }

    return $dump;
} // end function dump_sem($sem_id)


/**
 * Returns the regular dates for one seminar.
 * @param  $sem_id the id of the seminar
 * @return the HTML for the schedule table
 */
function dumpRegularDatesSchedule($sem_id)
{
    $presence_type_clause = getPresenceTypeClause();
    $query = "SELECT termine.*, themen.title AS th_title, themen.description AS th_desc
              FROM termine
              LEFT JOIN themen_termine USING (termin_id)
              LEFT JOIN themen USING (issue_id)
              WHERE range_id = ? AND date_typ IN {$presence_type_clause}
              ORDER BY date";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);

    return dumpScheduleTable($data, _('Ablaufplan'));
}

/**
 * Returns the extra dates for one seminar
 * @param  $sem_id the id of the seminar
 * @return the HTML for the schedule table for the extra dates
 */
function dumpExtraDatesSchedule($sem_id)
{
    $presence_type_clause = getPresenceTypeClause();
    $query = "SELECT termine.*, themen.title AS th_title, themen.description AS th_desc
              FROM termine
              LEFT JOIN themen_termine USING (termin_id)
              LEFT JOIN themen USING (issue_id)
              WHERE range_id = ? AND date_typ NOT IN {$presence_type_clause}
              ORDER BY date";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);

    return dumpScheduleTable($data, _('zusätzliche Termine'));
}

/**
 * Returns the schedule table for one query as HTML.
 * The query has to start like this:
 * SELECT termine.*, themen.title as th_title, themen.description as th_desc FROM termine LEFT JOIN themen_termine USING (termin_id) LEFT JOIN themen USING (issue_id)
 * @param  $data the result of an query for date entries
 * @param  $title the title for the table header
 * @return the HTML for the schedule table
 */
function dumpScheduleTable($data, $title)
{
    if (count($data) > 0) {
        $dump  = '<br>';
        $dump .= '<table width="100%" border="1" cellpadding="2" cellspacing="0">';
        $dump .= dumpDateTableHeader($title);
        $dump .= dumpDateTableRows($data);
        $dump .= '</table>' . "\n";
    }

    return $dump;
}

/**
 * Returns the first row (the header row) for the tables listing dates.
 * @param  $title title to show in first table row
 * @return the HTML for the first table row
 */
function dumpDateTableHeader($title)
{
    $dump  = '<tr><td colspan="2" align="left" class="table_header_bold">';
    $dump .= '<h2 class="table_header_bold">&nbsp;' . htmlReady($title) . '</h2>';
    $dump .= '</td></tr>' . "\n";

    return $dump;
}

/**
 * Returns the HTML table rows for the date entries in $data.
 * The query has to start like this:
 * SELECT termine.*, themen.title as th_title, themen.description as th_desc FROM termine LEFT JOIN themen_termine USING (termin_id) LEFT JOIN themen USING (issue_id)
 * @param  $data the result of an query for date entries
 * @return the HTML for the table rows
 */
function dumpDateTableRows($data)
{
    global $TERMIN_TYP;

    $dump = '';
    $lastTerminId = NULL;

    foreach ($data as $row) {
        $currentTerminId = $row['termin_id'];
        if ($lastTerminId != $currentTerminId) {
            $dump .= '<tr align="center"> ';
            $dump .= '<td width="25%" align="left" valign="top">';
            $dump .= strftime('%d. %b. %Y, %H:%M', $row['date']);
            $dump .= ' - ' . strftime('%H:%M', $row['end_time']);
            $dump .= '&nbsp;(' . $TERMIN_TYP[$row['date_typ']]['name'] . ')';
            $dump .= '</td>';
        } else {
            $dump .= '<tr><td width="25%"></td>';
        }

        $dump .= '<td width="75%" align="left"> ';
        $dump .= htmlReady($row['th_title'], 1, 1);
        if ($row['th_desc']) {
            $dump .= '<br/>';
            $dump .= formatReady($row['th_desc'], 1, 1);
        }
        $dump .= '&nbsp;</td></tr>' . "\n";

        $lastTerminId = $currentTerminId;
    }

    return $dump;
}


/////// die beiden Funktionen um das Forum zu exportieren

//Funktion zum archivieren eines Seminars, sollte in der Regel vor dem Loeschen ausgfuehrt werden.
function in_archiv ($sem_id)
{
    global $SEM_CLASS,$SEM_TYPE, $ARCHIV_PATH, $TMP_PATH, $_fullname_sql;

    NotificationCenter::postNotification('CourseWillArchive', $sem_id);

    //Besorgen der Grunddaten des Seminars
    $query = "SELECT Seminar_id, Name, Untertitel, Beschreibung,
                     start_time, Institut_id, status
              FROM seminare
              WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$sem_id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    $seminar_id     = $row['Seminar_id'];
    $name           = $row['Name'];
    $untertitel     = $row['Untertitel'];
    $beschreibung   = $row['Beschreibung'];
    $start_time     = $row['start_time'];
    $heimat_inst_id = $row['Institut_id'];

    //Besorgen von einzelnen Daten zu dem Seminar
    $all_semester = SemesterData::getAllSemesterData();
    foreach ($all_semester as $sem) {
        if (($start_time >= $sem['beginn']) && ($start_time <= $sem['ende'])) {
            $semester_tmp = $sem['name'];
        }
    }

    //Studienbereiche
    if ($SEM_CLASS[$SEM_TYPE[$row['status']]['class']]['bereiche']) {
        $sem_path = get_sem_tree_path($seminar_id);
        if (is_array($sem_path)) {
            $studienbereiche = join(', ', $sem_path);
        }
    }

    // das Heimatinstitut als erstes
    $query = "SELECT Name FROM Institute WHERE Institut_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$heimat_inst_id]);
    $institute = $statement->fetchColumn();

    // jetzt den Rest
    $query = "SELECT Name
              FROM Institute
              LEFT JOIN seminar_inst USING (institut_id)
              WHERE seminar_id = ? AND Institute.Institut_id != ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id, $heimat_inst_id]);
    while ($temp = $statement->fetchColumn()) {
        $institute .= ', ' . $temp;
    }

    $query = "SELECT GROUP_CONCAT({$_fullname_sql['full']} SEPARATOR ', ')
              FROM seminar_user
              LEFT JOIN auth_user_md5 USING (user_id)
              LEFT JOIN user_info USING (user_id)
              WHERE seminar_id = ? AND seminar_user.status = 'dozent'";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);
    $dozenten = $statement->fetchColumn();

    $query = "SELECT fakultaets_id
              FROM seminare
              LEFT JOIN Institute USING (Institut_id)
              WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);
    $fakultaet_id = $statement->fetchColumn();

    $query = "SELECT GROUP_CONCAT(DISTINCT c.Name SEPARATOR ' | ')
              FROM seminar_inst AS a
              LEFT JOIN Institute AS b USING (Institut_id)
              LEFT JOIN Institute AS c ON (c.Institut_id = b.fakultaets_id)
              WHERE a.seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);
    $fakultaet = $statement->fetchColumn();

    setTempLanguage();  // use DEFAULT_LANGUAGE for archiv-dumps

    //Dump holen
    $dump = dump_sem($sem_id, 'nobody');

    //Forumdump holen
    foreach (PluginEngine::getPlugins('ForumModule', $sem_id) as $plugin) {
        $forumdump .= $plugin->getDump($sem_id);
    }

    // Wikidump holen
    $wikidump = getAllWikiPages($sem_id, $name, FALSE);

    restoreLanguage();

    //OK, naechster Schritt: Kopieren der Personendaten aus seminar_user in archiv_user
    $query = "INSERT IGNORE INTO archiv_user (seminar_id, user_id, status)
              SELECT Seminar_id, user_id, status FROM seminar_user WHERE Seminar_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$seminar_id]);

    // Eventuelle Vertretungen in der Veranstaltung haben weiterhin Zugriff mit Dozentenrechten
    if (get_config('DEPUTIES_ENABLE')) {
        $deputies = getDeputies($seminar_id);
        // Eintragen ins Archiv mit Zugriffsberechtigung "dozent"
        $query = "INSERT IGNORE INTO archiv_user SET seminar_id = ?, user_id = ?, status = 'dozent'";
        $statement = DBManager::get()->prepare($query);
        foreach ($deputies as $deputy) {
            $statement->execute([$seminar_id, $deputy['user_id']]);
        }
    }


    //Archive files:


    //Get the top folder of the course:
    $top_folder = Folder::findTopFolder($sem_id);
    if($top_folder) {
        $top_folder = $top_folder->getTypedFolder();
    }

    //Collect all subfolders and files which are directly below the top folder:

    $readable_items = []; //files and folders which are readable for all course participants
    $protected_archive_items = []; //all files and folders of the course

    foreach($top_folder->getSubfolders() as $subfolder) {
        $protected_archive_items[] = $subfolder;
        if($subfolder instanceof StandardFolder) {
            //StandardFolder instances inside a course are always readable
            //for everyone. For other folder types we can't be sure
            //about that so that these folder types aren't included
            //in the standard file archive.
            $readable_items[] = $subfolder;
        }
    }

    foreach($top_folder->getFiles() as $file_ref) {
        $protected_archive_items[] = $file_ref;
        if($file_ref->terms_of_use) {
            if($file_ref->terms_of_use->download_condition == 0) {
                //only Files which are downloadable by everyone in the course
                //can be added to the standard file archive.
                $readable_items[] = $file_ref;
            }
        }
    }


    //Create the standard file archive if there are files and folders which
    //are readable (or downloadable) for everyone in the course:

    $archive_file_id = '';

    if (!empty($readable_items)) {
        //list of readable items isn't empty

        //create name for the archive ZIP file:
        $archive_file_id = md5('archive_' . $sem_id);

        $archive_path = $ARCHIV_PATH . '/' . $archive_file_id;

        FileArchiveManager::createArchive(
            $readable_items,
            'nobody',
            $archive_path,
            false, //don't do individual permission checks
            true, //keep hierarchy
            true //skip check for user permissions
        );

        if(!file_exists($archive_path)) {
            //empty archive or error during archive creation:
            $archive_file_id = ''; //no archive
        }

    }


    //Create the protected file archive which contains all files of the course.

    $archive_protected_files_zip_id = md5('protected_archive_' . $sem_id);

    $archive_protected_files_path = $ARCHIV_PATH . '/' . $archive_protected_files_zip_id;

    FileArchiveManager::createArchive(
        $protected_archive_items,
        null,
        $archive_protected_files_path,
        false //no permission checks
    );

    if(!file_exists($archive_protected_files_path)) {
        //empty archive or error during archive creation:
        $archive_protected_files_zip_id = ''; //no protected files archive
    }


    //We're done with archiving: Store a new archived course in the database:
    $query = "INSERT INTO archiv
                (seminar_id, name, untertitel, beschreibung, start_time,
                 semester, heimat_inst_id, institute, dozenten, fakultaet,
                 dump, archiv_file_id,archiv_protected_file_id, forumdump, wikidump, studienbereiche,
                 mkdate)
              VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
    $statement = DBManager::get()->prepare($query);
    $success = $statement->execute([
        $seminar_id,
        $name ?: '',
        $untertitel ?: '',
        $beschreibung ?: '',
        $start_time,
        $semester_tmp ?: '',
        $heimat_inst_id,
        $institute ?: '',
        $dozenten ?: '',
        $fakultaet ?: '',
        $dump ?: '',
        $archive_file_id ?: '',
        $archive_protected_files_zip_id ?: '',
        $forumdump ?: '',
        $wikidump ?: '',
        $studienbereiche ?: '',
    ]);
    if ($success) {
        NotificationCenter::postNotification('CourseDidArchive', $seminar_id);
    }
}
