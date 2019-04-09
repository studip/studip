<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
 * admission.inc.php
 *
 * the basic library for the admisson system
 *
 *
 * @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access       public
 * @modulegroup      admission
 * @module       admission.inc.php
 * @package      studip_core
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admission.inc.php
// Funktionen die zur Teilnehmerbeschraenkung benoetigt werden
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once 'lib/messaging.inc.php';
require_once 'lib/dates.inc.php';
require_once 'app/models/calendar/schedule.php';


/**
 * Insert a user into a seminar with optional log-message and contingent
 *
 * @param string   $seminar_id
 * @param string   $user_id
 * @param string   $status       status of user in the seminar (user, autor, tutor, dozent)
 * @param boolean  $copy_studycourse  if true, the studycourse is copied from admission_seminar_user
 *                                    to seminar_user. Overrides the $contingent-parameter
 * @param string   $contingent   optional studiengang_id, if no id is given, no contingent is considered
 * @param string   $log_message  optional log-message. if no log-message is given a default one is used
 * @return void
 */
function insert_seminar_user($seminar_id, $user_id, $status, $copy_studycourse = false, $contingent = false, $log_message = false) {
    // get the seminar-object
    $sem = Seminar::GetInstance($seminar_id);

    $admission_status = '';
    $admission_comment = '';
    $mkdate = time();

    $stmt = DBManager::get()->prepare("SELECT * FROM admission_seminar_user
            WHERE seminar_id = ? AND user_id = ?");
    $stmt->execute([$seminar_id, $user_id]);
    if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // copy the studycourse from admission_seminar_user
        if ($copy_studycourse && $data['studiengang_id']) {
            $contingent = $data['studiengang_id'];
        }
        $admission_status = $data['status'];
        $admission_comment = ($data['comment'] == NULL) ? '' : $data['comment'];
        $mkdate = $data['mkdate'];
    }

    // check if there are places left in the submitted contingent (if any)
    //ignore if preliminary
    if ($admission_status != 'accepted' && $contingent && $sem->isAdmissionEnabled() && !$sem->getFreeAdmissionSeats($contingent)) {
        return false;
    }

    // get coloured group as used on meine_seminare
    $colour_group = $sem->getDefaultGroup();

    // LOGGING
    // if no log message is submitted use a default one
    if (!$log_message) {
        $log_message = 'Wurde in die Veranstaltung eingetragen, admission_status: '. $admission_status . ' Kontingent: ' . $contingent;
    }
    StudipLog::log('SEM_USER_ADD', $seminar_id, $user_id, $status, $log_message);
    // actually insert the user into the seminar
    $stmt = DBManager::get()->prepare('INSERT IGNORE INTO seminar_user
        (Seminar_id, user_id, status, comment, gruppe, mkdate)
        VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$seminar_id, $user_id, $status, $admission_comment, $colour_group, $mkdate]);

    NotificationCenter::postNotification('UserDidEnterCourse', $seminar_id, $user_id);

    if ($admission_status) {
        // delete the entries, user is now in the seminar
        $stmt = DBManager::get()->prepare('DELETE FROM admission_seminar_user
            WHERE user_id = ? AND seminar_id = ?');
        $stmt->execute([$user_id, $seminar_id]);

        //renumber the waiting/accepted/lot list, a user was deleted from it
        renumber_admission($seminar_id);
    }
    $cs = $sem->getCourseSet();
    if ($cs) {
        $prio_delete = AdmissionPriority::unsetPriority($cs->getId(), $user_id, $sem->getId());
    }
    
    CalendarScheduleModel::deleteSeminarEntries($user_id, $seminar_id);

    // reload the seminar, the contingents have changed
    $sem->restore();

    // Check if a parent course exists and insert user there.
    if ($sem->parent_course) {
        insert_seminar_user($sem->parent_course, $user_id, $status, $copy_studycourse, $contingent, $log_message);
    }

    return true;
}


/**
 * This function numbers a waiting list
 *
 * Use this functions, if a person was moved from the waiting list or there were other changes
 * to the waiting list. The User gets a message, if the parameter is set and the position
 * on the waiting  list has changed.
 *
 * @param        string  seminar_id      the seminar_id of the seminar to calculate
 * @param        boolean send_message        should a system-message be send?
 *
 */

function renumber_admission ($seminar_id, $send_message = TRUE)
{
    $messaging = new messaging;

    $seminar = Seminar::GetInstance($seminar_id);
    if ($seminar->isAdmissionEnabled()) {
        //Liste einlesen
        $query = "SELECT user_id
                  FROM admission_seminar_user
                  WHERE seminar_id = ? AND status = 'awaiting'
                  ORDER BY position";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar->id]);
        $user_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        // Prepare statement that updates the position
        $query = "UPDATE admission_seminar_user
                  SET position = ?
                  WHERE user_id = ? AND seminar_id = ?";
        $update_statement = DBManager::get()->prepare($query);

        $position = 1;
        //Liste neu numerieren
        foreach ($user_ids as $user_id) {
            $update_statement->execute([$position, $user_id, $seminar->id]);

            //User benachrichten
            if ($update_statement->rowCount() && Config::get()->NOTIFY_ON_WAITLIST_ADVANCE && $send_message) {
                //Usernamen auslesen
                $username = get_username($user_id);

                setTempLanguage($user_id);
                $message = sprintf(_('Sie sind in der Warteliste der Veranstaltung **%s (%s)** hochgestuft worden. Sie stehen zur Zeit auf Position %s.'),
                    $seminar->name,
                    $seminar->getFormattedTurnus(),
                    $position);
                $subject = sprintf(_('Ihre Position auf der Warteliste der Veranstaltung %s wurde verändert'), $seminar->name);
                restoreLanguage();

                $messaging->insert_message($message, $username, '____%system%____', FALSE, FALSE, '1', FALSE, $subject);
            }
            $position += 1;
        }
    }
}


/*
 * This function is a kind of wrapper, so that no nasty loops between the updaters occur
 *
 **/
function update_admission ($seminar_id, $send_message = TRUE) {
    if(Seminar::GetInstance($seminar_id, TRUE)->isAdmissionEnabled()){
        normal_update_admission($seminar_id, $send_message);
    }
}

/**
 * This function updates an admission procedure
 *
 * The function checks, if user could be insert to the seminar.
 * The User gets a message, if he is inserted to the seminar
 *
 * @param        string  seminar_id      the seminar_id of the seminar to calculate
 * @param        boolean send_message        should a system-message be send?
 *
 */
function normal_update_admission($seminar_id, $send_message = TRUE)
{
    $messaging=new messaging;

    //Daten holen / Abfrage ob ueberhaupt begrenzt
    $seminar = Seminar::GetInstance($seminar_id);

    if($seminar->isAdmissionEnabled()){

        $sem_preliminary = ($seminar->admission_prelim == 1);
        $cs = $seminar->getCourseSet();
        //Veranstaltung einfach auffuellen (nach Lostermin und Ende der Kontingentierung)
        if (!$seminar->admission_disable_waitlist_move && $cs->hasAlgorithmRun()) {
            //anzahl der freien Plaetze holen
            $count = (int)$seminar->getFreeAdmissionSeats();

            //Studis auswaehlen, die jetzt aufsteigen koennen
            $query = "SELECT user_id, username
                      FROM admission_seminar_user
                      LEFT JOIN auth_user_md5 USING (user_id)
                      WHERE seminar_id = ? AND status = 'awaiting'
                      ORDER BY position
                      LIMIT " . (int)$count;
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$seminar->getId()]);
            $temp = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($temp as $row) {
                //ok, here ist the "colored-group" meant (for grouping on meine_seminare), not the grouped seminars as above!
                $group = select_group($seminar->getSemesterStartTime());

                if (!$sem_preliminary) {
                    $query = "INSERT INTO seminar_user
                                (user_id, Seminar_id, status, gruppe, mkdate)
                              VALUES (?, ?, 'autor', ?, UNIX_TIMESTAMP())";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([
                        $row['user_id'],
                        $seminar->getId(),
                        $group
                    ]);
                    $affected = $statement->rowCount();

                    NotificationCenter::postNotification('UserDidEnterCourse', $seminar->getId(), $row['user_id']);
                } else {
                    $query = "UPDATE admission_seminar_user
                              SET status = 'accepted'
                              WHERE user_id = ? AND seminar_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([
                        $row['user_id'],
                        $seminar->getId()
                    ]);
                    $affected = $statement->rowCount();
                }
                if ($affected > 0) {
                    $log_message = 'Wurde automatisch aus der Warteliste in die Veranstaltung eingetragen.';
                    StudipLog::log('SEM_USER_ADD', $seminar->getId(), $row['user_id'], $sem_preliminary ? 'accepted' : 'autor', $log_message);
                    if (!$sem_preliminary) {
                        $query = "DELETE FROM admission_seminar_user
                                  WHERE user_id = ? AND seminar_id = ?";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute([
                            $row['user_id'],
                            $seminar->getId()
                        ]);
                        $affected = $statement->rowCount();
                    } else {
                        $affected = 0;
                    }
                    //User benachrichtigen
                    if (($sem_preliminary || $affected > 0) && $send_message) {
                        setTempLanguage($row['user_id']);
                        if (!$sem_preliminary) {
                            $message = sprintf (_('Sie sind in die Veranstaltung **%s (%s)** eingetragen worden, da für Sie ein Platz frei geworden ist. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch für die Präsenzveranstaltung zugelassen.'), $seminar->getName(), $seminar->getFormattedTurnus(true));
                        } else {
                            $message = sprintf (_('Sie haben den Status vorläufig akzeptiert in der Veranstaltung **%s (%s)** erhalten, da für Sie ein Platz freigeworden ist.'), $seminar->getName(), $seminar->getFormattedTurnus(true));
                        }
                        $subject = sprintf(_("Teilnahme an der Veranstaltung %s"),$seminar->getName());
                        restoreLanguage();

                        $messaging->insert_message($message, $row['username'], '____%system%____', FALSE, FALSE, '1', FALSE, $subject, true);
                    }
                }
            }
            //Warteposition der restlichen User neu eintragen
            renumber_admission($seminar_id, FALSE);
        }
        $seminar->restore();
    }
}



/**
 * sets a user on a waiting list for a registration procedure
 *
 * if applicable ($status == 'awaiting') returns the position
 *
 * @param        string  user_id
 * @param        string  seminar_id
 * @param        string  status              'claiming','awaiting','accepted'
 * @param        string  studiengang_id
 * @param        string  comment
 * @return       integer position on waiting list
 *
 */
function admission_seminar_user_insert($user_id, $seminar_id, $status, $studiengang_id = '', $comment = '')
{
    if ($status == 'accepted') {
        $query = "INSERT INTO admission_seminar_user
                    (user_id, seminar_id, status, mkdate, comment)
                  VALUES (?, ?, ?, UNIX_TIMESTAMP(), ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $user_id,
            $seminar_id,
            $status,
            $comment
        ]);
        return $statement->rowCount();
    } elseif ($status == 'awaiting') {
        $query = "INSERT INTO admission_seminar_user
                    (user_id, seminar_id, status, mkdate, comment, position)
                  SELECT ?, ?, 'awaiting', UNIX_TIMESTAMP(), ?, IFNULL(MAX(position), 0) + 1
                  FROM admission_seminar_user
                  WHERE seminar_id = ? AND status != 'accepted'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $user_id,
            $seminar_id,
            $comment,
            $seminar_id
        ]);
    }
    return admission_seminar_user_get_position($user_id, $seminar_id);
}

/**
 * returns the position for a user on a waiting list
 *
 * if the user is not found false is returned, return true if the user is found but
 * no position is available
 *
 * @param        string  user_id
 * @param        string  seminar_id
 * @return       integer position in waiting list or false if not found
 *
 */
function admission_seminar_user_get_position($user_id, $seminar_id)
{
    $query = "SELECT IFNULL(position, 'na')
              FROM admission_seminar_user
              WHERE user_id = ? AND seminar_id = ? AND status='awaiting'";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user_id, $seminar_id]);
    $position = $statement->fetchColumn();

    return $position == 'na' ? true : $position;
}

