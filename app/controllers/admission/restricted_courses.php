<?php
/**
 * restricted_courses.php - administration of admission restrictions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

class Admission_RestrictedCoursesController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_('Teilnahmebeschränkte Veranstaltungen'));
        Navigation::activateItem('/tools/coursesets/restricted_courses');
    }

    /**
     * Shows a list of courses with restricted participant numbers.
     */
    function index_action()
    {

        $actions = new ActionsWidget();
        $actions->addLink(_("Export"), $this->url_for('admission/restricted_courses', ['csv' => 1]), Icon::create('file-excel+export', 'clickable'));
        Sidebar::get()->addWidget($actions);
        Sidebar::get()->setImage('sidebar/admin-sidebar.png');

        $sem_condition = "";
        foreach (words('current_institut_id sem_name_prefix') as $param) {
            $this->$param = $_SESSION[get_class($this)][$param];
        }
        if (Request::isPost()) {
            if (Request::submitted('choose_institut')) {
                $this->current_institut_id = Request::option('choose_institut_id');
                $this->current_semester_id = Request::option('select_semester_id');
                $this->sem_name_prefix = trim(Request::get('sem_name_prefix'));
            }
        }
        if (!$this->current_institut_id) {
            $this->current_institut_id = 'all';
        }
        if (!$this->current_semester_id) {
            $this->current_semester_id = $_SESSION['_default_sem'];
        } else {
            $_SESSION['_default_sem'] = $this->current_semester_id;
        }
        $semester = Semester::find($this->current_semester_id);
        $sem_condition .= "AND seminare.start_time <=" . (int)$semester["beginn"]." AND (" . (int)$semester["beginn"] . " <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
        if ($this->sem_name_prefix) {
            $sem_condition .= sprintf('AND (seminare.Name LIKE %1$s OR seminare.VeranstaltungsNummer LIKE %1$s) ', DBManager::get()->quote($this->sem_name_prefix . '%'));
        }
        if ($GLOBALS['perm']->have_perm('dozent')) {
            $this->my_inst = $this->get_institutes($sem_condition);
        }
        $this->courses = $this->get_courses($sem_condition);
        foreach (words('current_institut_id sem_name_prefix') as $param) {
            $_SESSION[get_class($this)][$param] = $this->$param;
        }
        if (Request::get('csv')) {
            $captions = [_("Anmeldeset"),
                    _("Nummer"),
                    _("Name"),
                    _("max. Teilnehmende"),
                    _("Teilnehmende aktuell"),
                    _("Anzahl Anmeldungen"),
                    _("Anzahl vorl. Anmeldungen"),
                    _("Anzahl Warteliste"),
                    _("Platzverteilung"),
                    _("Startzeitpunkt"),
                    _("Endzeitpunkt")];
            $data = [];
            foreach ($this->courses as $course) {
                $row = [];
                $row[] = $course['cs_name'];
                $row[] = $course['course_number'];
                $row[] = $course['course_name'];
                $row[] = (int)$course['admission_turnout'];
                $row[] = $course['count_teilnehmer'] + $course['count_prelim'];
                $row[] = (int)$course['count_claiming'];
                $row[] = (int)$course['count_prelim'];
                $row[] = (int)$course['count_waiting'];
                $row[] = $course['distribution_time'] ? strftime('%x %R', $course['distribution_time']) : '';
                $row[] = $course['start_time'] ? strftime('%x %R', $course['start_time']) : '';
                $row[] = $course['end_time'] ? strftime('%x %R', $course['end_time']) : '';
                $data[] = $row;
            }

            $tmpname = md5(uniqid('tmp'));
            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
                $this->redirect(
                    FileManager::getDownloadURLForTemporaryFile(
                        $tmpname,
                        'teilnahmebeschraenkteVeranstaltungen.csv'
                    )
                );
                return;
            }
        }
        if (is_array($this->not_distributed_coursesets)) {
            PageLayout::postMessage(MessageBox::info(
                _("Es existieren Anmeldesets, die zum Zeitpunkt der Platzverteilung nicht gelost wurden. Stellen Sie sicher, dass der Cronjob \"Losverfahren überprüfen\" ausgeführt wird."),
                array_unique($this->not_distributed_coursesets)));
        }
    }

    function get_courses($seminare_condition)
    {
        global $perm, $user;

        list($institut_id, $all) = explode('_', $this->current_institut_id);
        // Prepare count statements
        $query = "SELECT count(*)
                  FROM seminar_user
                  WHERE seminar_id = ? AND status IN ('user', 'autor')";
        $count0_statement = DBManager::get()->prepare($query);

        $query = "SELECT SUM(status = 'accepted') AS count2,
                     SUM(status = 'awaiting') AS count3
                  FROM admission_seminar_user
                  WHERE seminar_id = ?
                  GROUP BY seminar_id";
        $count1_statement = DBManager::get()->prepare($query);

        $parameters = [];

        $sql = "SELECT seminare.seminar_id,seminare.Name as course_name,seminare.VeranstaltungsNummer as course_number,
                admission_prelim, admission_turnout,seminar_courseset.set_id
                FROM seminar_courseset
                INNER JOIN courseset_rule csr ON csr.set_id=seminar_courseset.set_id AND csr.type='ParticipantRestrictedAdmission'
                INNER JOIN seminare ON seminar_courseset.seminar_id=seminare.seminar_id
                ";
        if ($institut_id == 'all'  && $perm->have_perm('root')) {
            $sql .= "WHERE 1 {$seminare_condition} ";
        } elseif ($all == 'all') {
            $sql .= "INNER JOIN Institute USING (Institut_id)
                    WHERE Institute.fakultaets_id = ? {$seminare_condition}
                    ";
            $parameters[] = $institut_id;
        } else {
            $sql .= "WHERE seminare.Institut_id = ? {$seminare_condition}
                    ";
            $parameters[] = $institut_id;
        }
        $sql .= "GROUP BY seminare.Seminar_id ORDER BY seminar_courseset.set_id, seminare.Name";

        $statement = DBManager::get()->prepare($sql);
        $statement->execute($parameters);
        $csets = [];
        $ret = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $seminar_id = $row['seminar_id'];
            $ret[$seminar_id] = $row;

            $count0_statement->execute([$seminar_id]);
            $count = $count0_statement->fetchColumn();

            $ret[$seminar_id]['count_teilnehmer']     = $count;

            $count1_statement->execute([$seminar_id]);
            $counts = $count1_statement->fetch(PDO::FETCH_ASSOC);

            $ret[$seminar_id]['count_prelim'] = (int)$counts['count2'];
            $ret[$seminar_id]['count_waiting']  = (int)$counts['count3'];
            if (!$csets[$row['set_id']]) {
                $csets[$row['set_id']] = new CourseSet($row['set_id']);
            }
            $cs = $csets[$row['set_id']];
            $ret[$seminar_id]['cs_name'] = $cs->getName();
            $ret[$seminar_id]['distribution_time'] = $cs->getSeatDistributionTime();
            if ($ret[$seminar_id]['distribution_time'] < (time() - 1000) && !$cs->hasAlgorithmRun()) {
                $this->not_distributed_coursesets[] = $cs->getName();
            }
            if ($ta = $cs->getAdmissionRule('TimedAdmission')) {
                $ret[$seminar_id]['start_time'] = $ta->getStartTime();
                $ret[$seminar_id]['end_time'] = $ta->getEndTime();
            }
            if (!$cs->hasAlgorithmRun()) {
                $ret[$seminar_id]['count_claiming'] = count(AdmissionPriority::getPrioritiesByCourse($row['set_id'], $seminar_id));
            }
        }
        return $ret;
    }

    function get_institutes($seminare_condition)
    {
        global $perm, $user;

        // Prepare institute statement
        $query = "SELECT a.Institut_id, a.Name, COUNT(courseset_rule.type) AS num_sem
        FROM Institute AS a
        LEFT JOIN seminare ON (seminare.Institut_id = a.Institut_id {$seminare_condition})
        LEFT JOIN seminar_courseset on seminar_courseset.seminar_id=seminare.seminar_id
        LEFT JOIN courseset_rule ON courseset_rule.type='ParticipantRestrictedAdmission' AND seminar_courseset.set_id=courseset_rule.set_id
        WHERE fakultaets_id = ? AND a.Institut_id != fakultaets_id
        GROUP BY a.Institut_id
        ORDER BY a.Name, num_sem DESC";
        $institute_statement = DBManager::get()->prepare($query);

        $parameters = [];
        if ($perm->have_perm('root')) {
            $query = "SELECT COUNT(*) FROM courseset_rule
                      INNER JOIN seminar_courseset on seminar_courseset.set_id=courseset_rule.set_id
                      INNER JOIN seminare ON seminar_courseset.seminar_id=seminare.seminar_id
                      WHERE courseset_rule.type='ParticipantRestrictedAdmission' {$seminare_condition}";
            $statement = DBManager::get()->query($query);
            $num_sem = $statement->fetchColumn();

            $_my_inst['all'] = [
                'name'    => _('alle'),
                'is_fak'  => true,
                'num_sem' => $num_sem,
            ];
            $query = "SELECT a.Institut_id, a.Name, 1 AS is_fak, COUNT(courseset_rule.type) AS num_sem
            FROM Institute AS a
            LEFT JOIN seminare ON (seminare.Institut_id = a.Institut_id {$seminare_condition})
            LEFT JOIN seminar_courseset on seminar_courseset.seminar_id=seminare.seminar_id
            LEFT JOIN courseset_rule ON courseset_rule.type='ParticipantRestrictedAdmission' AND seminar_courseset.set_id=courseset_rule.set_id
            WHERE a.Institut_id = fakultaets_id
            GROUP BY a.Institut_id
            ORDER BY is_fak, Name, num_sem DESC";
        } else {
            $query = "SELECT s.inst_perms,b.Institut_id, b.Name, b.Institut_id = b.fakultaets_id AS is_fak, COUNT( courseset_rule.type ) AS num_sem
            FROM user_inst AS s
            LEFT JOIN Institute AS b USING ( Institut_id )
            LEFT JOIN seminare ON ( seminare.Institut_id = b.Institut_id {$seminare_condition})
            LEFT JOIN seminar_courseset on seminar_courseset.seminar_id=seminare.seminar_id
            LEFT JOIN courseset_rule ON courseset_rule.type='ParticipantRestrictedAdmission' AND seminar_courseset.set_id=courseset_rule.set_id
            WHERE s.user_id = ? AND s.inst_perms IN ('admin', 'dozent')
            GROUP BY b.Institut_id
            ORDER BY is_fak, Name, num_sem DESC";
            $parameters[] = $user->id;
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $temp = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($temp as $row) {
            $_my_inst[$row['Institut_id']] = [
                    'name'    => $row['Name'],
                    'is_fak'  => $row['is_fak'],
                    'num_sem' => $row['num_sem']
            ];
            if ($row["is_fak"] && $row["inst_perms"] != 'dozent') {
                $institute_statement->execute([$row['Institut_id']]);
                $alle = $institute_statement->fetchAll();
                if (count($alle)) {
                    $_my_inst[$row['Institut_id'] . '_all'] = [
                            'name'    => sprintf(_('[Alle unter %s]'), $row['Name']),
                            'is_fak'  => 'all',
                            'num_sem' => $row['num_sem']
                    ];

                    $num_inst = 0;
                    $num_sem_alle = $row['num_sem'];


                    foreach ($alle as $institute) {
                        if(!$_my_inst[$institute['Institut_id']]) {
                            $num_inst += 1;
                            $num_sem_alle += $institute['num_sem'];
                        }
                        $_my_inst[$institute['Institut_id']] = [
                                'name'    => $institute['Name'],
                                'is_fak'  => 0,
                                'num_sem' => $institute["num_sem"]
                        ];
                    }
                    $_my_inst[$row['Institut_id']]['num_inst']          = $num_inst;
                    $_my_inst[$row['Institut_id'] . '_all']['num_inst'] = $num_inst;
                    $_my_inst[$row['Institut_id'] . '_all']['num_sem']  = $num_sem_alle;
                }
            }
        }
        return $_my_inst;
    }
}
