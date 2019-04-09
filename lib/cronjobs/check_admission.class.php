<?php
/**
* check_admission.class.php
*
* @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access public
* @since  2.4
*/

class CheckAdmissionJob extends CronJob
{
    public static function getName()
    {
        return _('Losverfahren überprüfen');
    }

    public static function getDescription()
    {
        return _('Überprüft, ob Losverfahren anstehen und führt diese aus');
    }

    public static function getParameters()
    {
        return [
            'verbose' => [
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden (sind später im Log des Cronjobs sichtbar)'),
            ],
            'send_messages' => [
                'type'        => 'boolean',
                'default'     => true,
                'status'      => 'optional',
                'description' => _('Sollen interne Nachrichten an alle betroffenen Nutzer gesendet werden)'),
            ],
        ];
    }

    public function setUp()
    {
        require_once 'lib/classes/admission/CourseSet.class.php';
        if (empty($GLOBALS['ABSOLUTE_URI_STUDIP'])) {
            throw new Exception('To use check_admission job you MUST set correct values for $ABSOLUTE_URI_STUDIP in config_local.inc.php!');
        }
    }

    public function execute($last_result, $parameters = [])
    {
        $verbose = $parameters['verbose'];

        $query = "SELECT DISTINCT cr.set_id
                  FROM courseset_rule AS cr
                  INNER JOIN coursesets USING(set_id)
                  WHERE type = 'ParticipantRestrictedAdmission'
                    AND algorithm_run = 0";
        $sets = DbManager::get()->fetchFirst($query);
        if (count($sets) > 0) {
            if ($verbose) {
                echo date('r') . ' - Starting seat distribution ' . chr(10);
                $old_logger = Log::get()->getHandler();
                $old_log_level = Log::get()->getLogLevel();
                @mkdir($GLOBALS['TMP_PATH'] . '/seat_distribution_logs');
                $logfile = $GLOBALS['TMP_PATH'] . '/seat_distribution_logs/' .  date('Y-m-d-H-i') . '_seat_distribution.log';
                if (is_dir($GLOBALS['TMP_PATH'] . '/seat_distribution_logs')) {
                    Log::get()->setHandler($logfile);
                    Log::get()->setLogLevel(Log::DEBUG);
                    echo 'logging to ' . $logfile . chr(10);
                } else {
                    echo 'could not create directory ' . $GLOBALS['TMP_PATH'] . '/seat_distribution_logs' . chr(10);
                }
            }
            foreach ($sets as $set_id) {
                $courseset = new CourseSet($set_id);
                if ($courseset->isSeatDistributionEnabled() && !$courseset->hasAlgorithmRun() && $courseset->getSeatDistributionTime() < time()) {
                    if ($verbose) {
                        echo ++$i . ' ' . $courseset->getId() . ' : ' . $courseset->getName() . chr(10);
                        $applicants = AdmissionPriority::getPriorities($set_id);
                        $courses = SimpleCollection::createFromArray(Course::findMany($courseset->getCourses()))->toGroupedArray('seminar_id', words('name veranstaltungsnummer'));
                        $captions = [_("Nachname"), _("Vorname"), _("Nutzername"),_('Nutzer-ID'), _('Veranstaltung-ID'), _("Veranstaltung"), _("Nummer"), _("Priorität")];
                        $data = [];
                        $users = User::findEachMany(function($user) use ($courses,$applicants,&$data) {
                            $app_courses = $applicants[$user->id];
                            asort($app_courses);
                            foreach ($app_courses as $course_id => $prio) {
                                $row = [];
                                $row[] = $user->nachname;
                                $row[] = $user->vorname;
                                $row[] = $user->username;
                                $row[] = $user->id;
                                $row[] = $course_id;
                                $row[] = $courses[$course_id]['name'];
                                $row[] = $courses[$course_id]['veranstaltungsnummer'];
                                $row[] = $prio;
                                $data[] = $row;
                            }
                        }, array_keys($applicants), 'ORDER BY Nachname,Vorname');
                        $applicants_file = $GLOBALS['TMP_PATH'] . '/seat_distribution_logs/applicants_' . $set_id . '.csv';
                        if (array_to_csv($data, $applicants_file, $captions)) {
                            echo 'applicants written to ' . $applicants_file . chr(10);
                        }
                    }
                    $courseset->distributeSeats();
                }
            }
            if ($verbose) {
                Log::get()->setHandler($old_logger);
                Log::get()->setLogLevel($old_log_level);
            }
        } else {
            if ($verbose) {
                echo date('r') . ' - Nothing to do' . chr(10);
            }
        }
    }
}
