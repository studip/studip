<?php
class MembersModel
{

    protected $course_id;
    protected $course_title;

    public function __construct($course_id, $course_title)
    {
        $this->course_id = $course_id;
        $this->course_title = $course_title;
    }

    public function setAdmissionVisibility($user_id, $status)
    {
        $query = "UPDATE admission_seminar_user SET visible = '?' WHERE user_id = ? AND seminar_id = ?";
        $statement = DBManager::get()->prepare($query);

        return $statement->execute([$status, $user_id, $this->course_id]);
    }

    public function setVisibilty($user_id, $status)
    {

        $query = "UPDATE seminar_user SET visible = ? WHERE user_id = ? AND Seminar_id = ?";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([$status, $user_id, $this->course_id]);

        return $statement->rowCount();
    }

    public function setMemberStatus($members, $status, $next_status, $direction)
    {
        $msgs = [];
        $query = 'UPDATE seminar_user SET status = ?, position = ? WHERE Seminar_id = ? AND user_id = ? AND status = ?';
        $pleasure_statement = DBManager::get()->prepare($query);

        foreach ($members as $user_id) {
            $temp_user = User::find($user_id);
            if ($next_status == 'tutor' && !$GLOBALS['perm']->have_perm('tutor', $user_id)) {
                $msgs['no_tutor'][$user_id] = $temp_user->getFullName();
            } else {
                if ($temp_user) {
                    // get the next position of the user
                    switch ($next_status) {
                        case 'user':
                            // get the current position of the user
                            $next_pos = $this->getPosition($user_id);
                            break;
                        case 'autor':
                            // get the current position of the user
                            $next_pos = $this->getPosition($user_id);
                            break;
                        // set the status to tutor
                        case 'tutor':

                            // get the next position of the user
                            $next_pos = get_next_position($next_status, $this->course_id);
                            // resort the tutors
                            re_sort_tutoren($this->course_id, $this->getPosition($user_id));
                            break;
                    }

                    StudipLog::log('SEM_CHANGED_RIGHTS', $this->course_id, $user_id, $next_status,
                            $this->getLogLevel($direction, $next_status));
                    NotificationCenter::postNotification('CourseMemberStatusDidUpdate', $this->course_id, $user_id);

                    if (is_null($next_pos)) {
                        $next_pos = 0;
                    }

                    $pleasure_statement->execute([$next_status, $next_pos, $this->course_id, $user_id, $status]);

                    if ($pleasure_statement->rowCount()) {
                        if ($next_status == 'autor') {
                            re_sort_tutoren($this->course_id, $next_pos);
                        }
                        $msgs['success'][$user_id] = $temp_user->getFullName();
                    }
                }
            }
        }

        if (!empty($msgs)) {
            return $msgs;
        } else {
            return false;
        }
    }

    public function cancelSubscription($users)
    {
        $sem = Seminar::GetInstance($this->course_id);
        $messaging = new messaging;
        foreach ($users as $user_id) {
            // delete member from seminar
            if ($sem->deleteMember($user_id)) {
                $user = User::find($user_id);
                setTempLanguage($user_id);
                $message = sprintf(_('Ihre Anmeldung zur Veranstaltung **%1$s** wurde von Lehrenden  (%2$s) oder Admin aufgehoben.'), $this->course_title, get_title_for_status('dozent', 1));
                restoreLanguage();
                $messaging->insert_message($message, $user->username,
                                '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'),
                                        _("Anmeldung aufgehoben")), TRUE);
                $msgs[] = $user->getFullName();
            }
        }

        return $msgs;
    }

    public function cancelAdmissionSubscription($users, $status)
    {
        $messaging = new messaging;
        $query = "DELETE FROM admission_seminar_user WHERE seminar_id = ? AND user_id = ? AND status = ?";
        $db = DBManager::get()->prepare($query);
        $cs = Seminar::GetInstance($this->course_id)->getCourseSet();
        foreach ($users as $user_id) {
            $user = User::find($user_id);
            if ($cs) {
                $prio_delete = AdmissionPriority::unsetPriority($cs->getId(), $user_id, $this->course_id);
            }
            $db->execute([$this->course_id, $user_id, $status]);
            if ($db->rowCount() > 0 || $prio_delete) {
                setTempLanguage($user_id);
                if ($status !== 'accepted') {
                    $message = sprintf(_('Sie wurden von %1$s oder Admin von der Warteliste der Veranstaltung **%2$s** gestrichen und sind damit __nicht__ zugelassen worden.'), get_title_for_status('dozent', 1),  $this->course_title);
                } else {
                    $message = sprintf(_('Sie wurden von %1$s oder Admin aus der Veranstaltung **%2$s** gestrichen und sind damit __nicht__ zugelassen worden.'), get_title_for_status('dozent', 1), $this->course_title);
                }
                restoreLanguage();
                $messaging->insert_message($message, $user->username,
                                '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'),
                                        _("nicht zugelassen in Veranstaltung")), TRUE);
                StudipLog::log('SEM_USER_DEL', $this->course_id, $user_id, 'Wurde aus der Veranstaltung entfernt');
                NotificationCenter::postNotification('UserDidLeaveCourse', $this->course_id, $user_id);

                $msgs[] = $user->getFullName();
            }
        }
        return $msgs;
    }

    public function insertAdmissionMember($users, $next_status, $consider_contingent, $accepted = null, $cmd = 'add_user')
    {
        $messaging = new messaging;
        foreach ($users as $user_id => $value) {
            if ($value) {
                $user = User::find($user_id);
                if ($user) {
                    $admission_user = insert_seminar_user($this->course_id, $user_id, $next_status,
                            ($accepted || $consider_contingent ? TRUE : FALSE), $consider_contingent);

                    // only if user was on the waiting list
                    if ($admission_user) {
                        setTempLanguage($user_id);
                        restoreLanguage();

                        if ($cmd == "add_user") {
                            $message = sprintf(_('Sie wurden von %1$s oder Admin
                                in die Veranstaltung **%2$s** eingetragen.'), get_title_for_status('dozent', 1), $this->course_title);
                        } else {
                            if (!$accepted) {
                                $message = sprintf(_('Sie wurden von %1$s oder Admin
                                    aus der Warteliste in die Veranstaltung **%2$s** aufgenommen und sind damit zugelassen.'),
                                        get_title_for_status('dozent', 1), $this->course_title);
                            } else {
                                $message = sprintf(_('Sie wurden von einem/einer %1$s oder Admin
                                    vom Status **vorläufig akzeptiert** auf **teilnehmend** in der Veranstaltung **%2$s**
                                    hochgestuft und sind damit zugelassen.'), get_title_for_status('dozent', 1), $this->course_title);
                            }
                        }

                        $messaging->insert_message($message, $user->username,
                                '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'),
                                        _('Eintragung in Veranstaltung')), TRUE);
                        $msgs[] = $user->getFullName();
                    }
                }
            }
        }

        // resort admissionlist
        renumber_admission($this->course_id);

        return $msgs;
    }

    public function addMember($user_id, $accepted = null, $consider_contingent = null, $cmd = 'add_user')
    {
        global $perm, $SEM_CLASS, $SEM_TYPE;

        $user = User::find($user_id);
        $messaging = new messaging;

        $status = 'autor';

        // insert
        $copy_course = ($accepted || $consider_contingent) ? TRUE : FALSE;
        $admission_user = insert_seminar_user($this->course_id, $user_id, $status, $copy_course, $consider_contingent, true);

        if ($admission_user) {
            setTempLanguage($user_id);
            if ($cmd == 'add_user') {
                $message = sprintf(_('Sie wurden vom einem/einer %1$s oder Admin
                    in die Veranstaltung **%2$s** eingetragen.'), get_title_for_status('dozent', 1), $this->course_title);
            } else {
                if (!$accepted) {
                    $message = sprintf(_('Sie wurden vom einem/einer %1$s oder Admin
                        aus der Warteliste in die Veranstaltung **%2$s** aufgenommen und sind damit zugelassen.'),
                            get_title_for_status('dozent', 1), $this->course_title);
                } else {
                    $message = sprintf(_('Sie wurden von einem/einer %1$s oder Admin vom Status
                        **vorläufig akzeptiert** auf "**teilnehmend** in der Veranstaltung **%2$s**
                        hochgestuft und sind damit zugelassen.'), get_title_for_status('dozent', 1), $this->course_title);
                }
            }
            restoreLanguage();
            $messaging->insert_message($message, $user->username,
                    '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'),
                            _('Eintragung in Veranstaltung')), TRUE);
        }

        //Warteliste neu sortieren
        renumber_admission($this->course_id);

        if ($admission_user) {
            if ($cmd == 'add_user') {
                $msg = MessageBox::success(sprintf(_('%1$s wurde in die Veranstaltung mit dem Status
                    <b>%2$s</b> eingetragen.'), $user->getFullName(), $status));
            } else {
                if (!$accepted) {
                    $msg = MessageBox::success(sprintf(_('%1$s wurde aus der Anmelde bzw. Warteliste
                        mit dem Status <b>%2$s</b> in die Veranstaltung eingetragen.'), $user->getFullName(), $status));
                } else {
                    $msg = MessageBox::success(sprintf(_('%1$s wurde mit dem Status <b>%2$s</b>
                        endgültig akzeptiert und damit in die Veranstaltung aufgenommen.'), $user->getFullName(), $status));
                }
            }
        } else if ($consider_contingent) {
            $msg = MessageBox::error(_('Es stehen keine weiteren Plätze mehr im Teilnehmendenkontingent zur Verfügung.'));
        } else {
            $msg = MessageBox::error(_('Beim Eintragen ist ein Fehler aufgetreten.
                Bitte versuchen Sie es erneut oder wenden Sie sich an einen Systemadministrator'));
        }

        return $msg;
    }

    /**
     * Adds the given user to the waitlist of the current course and sends a
     * corresponding message.
     *
     * @param String $user_id The user to add
     * @return bool Successful operation?
     */
    public function addToWaitlist($user_id)
    {
        $course = Seminar::getInstance($this->course_id);
        // Insert user in waitlist at current position.
        if ($course->addToWaitlist($user_id, 'last')) {
            setTempLanguage($user_id);
            $message = sprintf(_('Sie wurden von einem/einer Veranstaltungsleiter/-in (%1$s) ' .
                'oder einem/einer Administrator/-in auf die Warteliste der Veranstaltung **%2$s** gesetzt.'),
                get_title_for_status('dozent', 1), $this->course_title);
            restoreLanguage();
            messaging::sendSystemMessage($user_id, sprintf('%s %s', _('Systemnachricht:'),
                    _('Auf Warteliste gesetzt')), $message);

            return true;
        }
        return false;
    }

    /**
     * Adds the given users to the target course.
     * @param array $users users to add
     * @param string $target_course which course to add users to
     * @param bool $move move users (=delete in source course) or just add to target course?
     * @return array success and failure statuses
     */
    public function sendToCourse($users, $target_course_id, $move = false)
    {
        $msg = [];
        foreach ($users as $user) {
            if (!CourseMember::exists([$target_course_id, $user])) {
                $target_course = new Seminar($target_course_id);
                if ($target_course->addMember($user)) {
                    if ($move) {
                        $remove_from = Seminar::getInstance($this->course_id);
                        $remove_from->deleteMember($user);
                    }
                    $msg['success'][] = $user;
                } else {
                    $msg['failed'][] = $user;
                }
            } else {
                $msg['existing'][] = $user;
            }
        }
        return $msg;
    }

    /**
     * Get user informations by first and last name for csv-import
     * @param String $vorname
     * @param String $nachname
     * @return Array
     */
    public function getMemberByIdentification($nachname, $vorname = null)
    {
        // TODO Fullname
        $query = "SELECT a.user_id, username, perms, b.Seminar_id AS is_present
                 FROM auth_user_md5 AS a
                 LEFT JOIN user_info USING (user_id)
                 LEFT JOIN seminar_user AS b ON (b.user_id = a.user_id AND b.Seminar_id = ?)
                 WHERE perms IN ('autor', 'tutor', 'dozent')
                 AND a.visible <> 'never'
                 AND Nachname LIKE ? AND (? IS NULL OR Vorname LIKE ?)
                 ORDER BY Nachname, Vorname";
        $db = DBManager::get()->prepare($query);

        $db->execute([$this->course_id, $nachname, $vorname, $vorname]);

        return $db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user informations by username for csv-import
     * @param String $username
     * @return Array
     */
    public function getMemberByUsername($username)
    {
        // TODO Fullname
        $query = "SELECT a.user_id, username,
                        perms, b.Seminar_id AS is_present
                 FROM auth_user_md5 AS a
                 LEFT JOIN user_info USING (user_id)
                 LEFT JOIN seminar_user AS b ON (b.user_id = a.user_id AND b.Seminar_id = ?)
                 WHERE perms IN ('autor', 'tutor', 'dozent')
                 AND a.visible <> 'never'
                   AND username LIKE ?
                 ORDER BY Nachname, Vorname";
        $db = DBManager::get()->prepare($query);
        $db->execute([$this->course_id, $username]);

        return $db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user informations by generic datafields for csv-import
     * @param String $nachname
     * @param String $datafield_id
     * @return Array
     */
    public function getMemberByDatafield($nachname, $datafield_id)
    {
        // TODO Fullname
        $query = "SELECT a.user_id, username, b.Seminar_id AS is_present
                 FROM datafields_entries AS de
                 LEFT JOIN auth_user_md5 AS a ON (a.user_id = de.range_id)
                 LEFT JOIN user_info USING (user_id)
                 LEFT JOIN seminar_user AS b ON (b.user_id = a.user_id AND b.Seminar_id = ?)
                 WHERE perms IN ('autor', 'tutor', 'dozent')
                 AND a.visible <> 'never'
                   AND de.datafield_id = ? AND de.content = ?
                 ORDER BY Nachname, Vorname";
        $db = DBManager::get()->prepare($query);
        $db->execute([$this->course_id, $datafield_id, $nachname]);
        return $db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sort_status
     * @param string $order_by
     * @param string $exclude_invisibles
     * @return SimpleCollection
     */
    public function getMembers($sort_status = 'autor', $order_by = 'nachname asc')
    {
        list($order, $asc) = explode(' ', $order_by);
        if ($order === 'nachname') {
            $order_by = "Nachname {$asc},Vorname {$asc}";
        }

        $query = "SELECT su.user_id, username, Vorname, Nachname, Email, status,
                         position, su.mkdate, su.visible, su.comment,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                  FROM seminar_user AS su
                  INNER JOIN auth_user_md5 USING (user_id)
                  INNER JOIN user_info USING (user_id)
                  WHERE seminar_id = ?
                  ORDER BY position, Nachname ASC";
        $st = DBManager::get()->prepare($query);
        $st->execute([$this->course_id]);
        $members = SimpleCollection::createFromArray($st->fetchAll(PDO::FETCH_ASSOC));
        $filtered_members = [];

        foreach (words('user autor tutor dozent') as $status) {
            $filtered_members[$status] = $members->findBy('status', $status);
            if ($status === $sort_status) {
                $filtered_members[$status]->orderBy($order_by, $order !== 'nachname' ? SORT_NUMERIC : SORT_LOCALE_STRING);
            } else {
                $filtered_members[$status]->orderBy(in_array($status, words('tutor dozent')) ? 'position,Nachname,Vorname' : 'Nachname,Vorname');
            }
        }
        return $filtered_members;
    }

    /**
     * @param string $sort_status
     * @param string $order_by
     * @return SimpleCollection
     */
    public function getAdmissionMembers($sort_status = 'autor', $order_by = 'nachname asc')
    {
        list($order, $asc) = explode(' ', $order_by);
        if ($order === 'nachname') {
            $order_by = "nachname {$asc},vorname {$asc}";
        }

        $cs = CourseSet::getSetForCourse($this->course_id);
        $claiming = [];
        if (is_object($cs) && !$cs->hasAlgorithmRun()) {
            foreach (AdmissionPriority::getPrioritiesByCourse($cs->getId(), $this->course_id) as $user_id => $p) {
                $user = User::find($user_id);
                $data = $user->toArray('user_id username vorname nachname email');
                $data['fullname'] = $user->getFullname('full_rev');
                $data['position'] = $cs->hasAdmissionRule('LimitedAdmission') ? $p : '-';
                $data['visible'] = 'unknown';
                $data['status'] = 'claiming';
                $claiming[] = $data;
            }
        }

        $query = "SELECT asu.user_id, username, Vorname, Nachname, Email, status,
                         position, asu.mkdate, asu.visible, asu.comment,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                  FROM admission_seminar_user AS asu
                  INNER JOIN auth_user_md5 USING (user_id)
                  INNER JOIN user_info USING (user_id)
                  WHERE seminar_id = ?
                  ORDER BY position, Nachname ASC";
        $st = DBManager::get()->prepare($query);
        $st->execute([$this->course_id]);
        $application_members = SimpleCollection::createFromArray(array_merge($claiming, $st->fetchAll(PDO::FETCH_ASSOC)));
        $filtered_members = [];
        foreach (words('awaiting accepted claiming') as $status) {
            $filtered_members[$status] = $application_members->findBy('status', $status);
            if ($status === $sort_status) {
                $filtered_members[$status]->orderBy($order_by, $order !== 'nachname' ? SORT_NUMERIC : SORT_LOCALE_STRING);
            }
        }
        return $filtered_members;
    }

    /**
     * Adds given users to the course waitlist, either at list beginning or end.
     * System messages are sent to affected users.
     *
     * @param mixed $users array of user ids to add
     * @param String $which_end 'last' or 'first': which list end to append to
     * @return mixed Array of messages (stating success and/or errors)
     */
     public function moveToWaitlist($users, $which_end)
     {
         $course = Seminar::getInstance($this->course_id);
         $enrolment_positioning = [];
         $courseMembers = CourseMember::findBySQL('user_id IN (?) AND Seminar_id = ? ORDER BY mkdate ASC' , [$users, $this->course_id]);
         foreach($courseMembers as $member){
            $enrolment_positioning[$member->user_id] = $member->mkdate; // using mkdate for development (debug)
         }
         $current_pos = 0;
         $total_user = count($enrolment_positioning); 
         foreach ($enrolment_positioning as $user_id => $en_date) {
             // Delete member from seminar
             if ($course->deleteMember($user_id)) {
                 setTempLanguage($user_id);
                 $message = sprintf(_('Sie wurden von der Veranstaltung **%s** von '.
                     '%s oder der Administration abgemeldet, '.
                     'Sie wurden auf die Warteliste dieser Veranstaltung gesetzt.'),
                     $this->course_title, get_title_for_status('dozent', 1));
                 restoreLanguage();
                 messaging::sendSystemMessage($user_id, sprintf('%s %s', _('Systemnachricht:'),
                     _('Anmeldung aufgehoben, auf Warteliste gesetzt')), $message);
                 // Insert user in waitlist at current position.
                 $temp_user = User::find($user_id);
                 $current_pos = $course->addToWaitlist($user_id, $which_end, $current_pos, $total_user);
                 if ($current_pos) {
                     $msgs['success'][] = $temp_user->getFullname('no_title');
                 } else {
                     // Something went wrong on removing the user from course.
                     $msgs['error'][] = $temp_user->getFullname('no_title');
                 }
                 // Something went wrong on inserting the user in waitlist.
             } else {
                 $msgs['error'][] = $temp_user->getFullname('no_title');
             }
         }
         return $msgs;
     }

    /**
     * Get the positon out of the database
     * @param String $user_id
     * @return String
     */
    private function getPosition($user_id)
    {
        $query = "SELECT position FROM seminar_user WHERE user_id = ?";
        $position_statement = DBManager::get()->prepare($query);

        $position_statement->execute([$user_id]);
        $pos = $position_statement->fetchColumn();
        $position_statement->closeCursor();

        if ($pos) {
            return $pos;
        } else {
            return null;
        }
    }

    private function getLogLevel($direction, $status)
    {
        if ($direction == 'upgrade') {
            $directionString = 'Hochgestuft';
        } else {
            $directionString = 'Runtergestuft';
        }

        switch ($status) {
            case 'tutor': $log_level = 'zum Tutor';
                break;
            case 'autor': $log_level = 'zum Autor';
                break;
            case 'dozent': $log_level = 'zum Dozenten';
                break;
        }

        return sprintf('%s %s', $directionString, $log_level);
    }

    /*
    * set the user_visibility of all unkowns to their global visibility
    * set tutor and dozent to visible=yes
    */
    function checkUserVisibility()
    {
        $st = DBManager::get()->prepare("SELECT COUNT(*) FROM seminar_user WHERE visible = 'unknown' AND Seminar_id = ?");
        $st->execute([$this->course_id]);
        if ($st->fetchColumn()) {
            $st = DBManager::get()->prepare("UPDATE seminar_user SET visible = 'yes' WHERE status IN ('tutor', 'dozent') AND Seminar_id = ?");
            $st->execute([$this->course_id]);

            $st = DBManager::get()->prepare("UPDATE seminar_user su INNER JOIN auth_user_md5 aum USING(user_id)
                SET su.visible=IF(aum.visible IN('no','never') OR (aum.visible='unknown' AND " . (int)!Config::get()->USER_VISIBILITY_UNKNOWN . "), 'no','yes')
                WHERE Seminar_id = ? AND su.visible='unknown'");
            $st->execute([$this->course_id]);
        }
    }

}
