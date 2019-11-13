<?php
require_once 'lib/raumzeit/raumzeit_functions.inc.php';

class Course_DatesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        PageLayout::setHelpKeyword('Basis/InVeranstaltungAblauf');

        parent::before_filter($action, $args);
        checkObject();
        checkObjectModule('schedule');

        $this->course = Context::get();
        if ($this->course) {
            PageLayout::setTitle($this->course->getFullname() . ' ' . _('Termine'));
        } else {
            PageLayout::setTitle(_('Termine'));
        }

        Sidebar::get()->setImage('sidebar/date-sidebar.png');

        $this->show_raumzeit = $this->course->getSemClass()->offsetGet('show_raumzeit');
        $this->has_access    = $this->hasAccess();
    }

    public function index_action()
    {
        if ($this->hasAccess() && Request::isPost() && Request::option('termin_id') && Request::get('topic_title')) {
            $date = new CourseDate(Request::option('termin_id'));
            $seminar_id = $date['range_id'];
            $title = Request::get('topic_title');
            $topic = CourseTopic::findByTitle($seminar_id, $title);
            if (!$topic) {
                $topic = new CourseTopic();
                $topic['title']       = $title;
                $topic['seminar_id']  = $seminar_id;
                $topic['author_id']   = $GLOBALS['user']->id;
                $topic['description'] = '';
                $topic->store();
            }
            $success = $date->addTopic($topic);
            if ($success) {
                PageLayout::postSuccess(_('Thema wurde hinzugefügt.'));
            } else {
                PageLayout::postInfo(_('Thema war schon mit dem Termin verknüpft.'));
            }
        }
        Navigation::activateItem('/course/schedule/dates');

        object_set_visit_module('schedule');
        $this->assignLockRulesToTemplate();

        $this->last_visitdate = object_get_visit($this->course->id, 'schedule');
        $this->dates          = $this->course->getDatesWithExdates();


        // set up sidebar
        $actions = new ActionsWidget();

        if (!$this->show_raumzeit && $this->hasAccess()) {
            $actions->addLink(
                _('Neuer Einzeltermin'),
                $this->url_for('course/dates/singledate'),
                Icon::create('add')
            )->asDialog();
        }

        if (Seminar::setInstance(new Seminar(Course::findCurrent()))->getSlotModule('documents') && CourseDateFolder::availableInRange(Course::findCurrent(), User::findCurrent()->id)) {
            $actions->addLink(
                _('Sitzungsordner anlegen'),
                $this->url_for('course/dates/create_folders'),
                Icon::create('folder-topic-empty')
            )->asDialog('size=auto');
        }
        $actions->addLink(
            _('Als Doc-Datei runterladen'),
            $this->url_for('course/dates/export'),
            Icon::create('file-word')
        );

        $actions->addLink(
            _('Als CSV-Datei exportieren'),
            $this->url_for('course/dates/export_csv'),
            Icon::create('file-excel')
        );

        Sidebar::get()->addWidget($actions);

        if ($this->show_raumzeit && $this->hasAccess()) {
            $actions = new LinksWidget();
            $actions->setTitle(_('Links'));
            $actions->addLink(_('Zeiten und Räume bearbeiten'),
                              $this->url_for('course/timesrooms'),
                              Icon::create('link-intern'));
            Sidebar::get()->addWidget($actions);
        }
    }


    /**
     * This method is called to show the dialog to edit a date for a course.
     *
     * @param String $termin_id    The id of the date
     * @return void
     */
    public function details_action($termin_id)
    {
        $this->date = new CourseDate($termin_id);

        Navigation::activateItem('/course/schedule/dates');
        PageLayout::setTitle(
            $this->date->getTypeName() . ': ' .
            $this->date->getFullname(CourseDate::FORMAT_VERBOSE)
        );

        if ($this->hasAccess()) {
            $this->assignLockRulesToTemplate();

            $this->teachers = array_map(function (CourseMember $member) {
                return $member->user;
            }, $this->date->course->getMembersWithStatus('dozent'));
            $this->assigned_teachers = $this->date->dozenten;

            $this->groups          = $this->date->course->statusgruppen;
            $this->assigned_groups = $this->date->statusgruppen;

            $this->render_action('details-edit');
        }
    }

    public function details_files_action($termin_id)
    {
        $this->date = new CourseDate($termin_id);

        Navigation::activateItem('/course/schedule/dates');
        PageLayout::setTitle(
            $this->date->getTypeName() . ': ' .
            $this->date->getFullname(CourseDate::FORMAT_VERBOSE)
        );
        $this->render_action('details');
    }

    /**
     * This method is called to show the dialog to edit a singledate for a studygroup.
     *
     * @param String $termin_id The id of the date
     * @return void
     */
    public function singledate_action($termin_id = null)
    {
        $this->checkAccess();
        $this->assignLockRulesToTemplate();

        Navigation::activateItem('/course/schedule/dates');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $start_date = strtotime(Request::get('startDate'));

            $errors = [];
            if (!$start_date) {
                $errors[] = _('Bitte geben Sie ein korretes Datum an!');
            } else {
                $start_time = strtotime((Request::get('start_stunde') ?: '0') . ':' . (Request::get('start_minute') ?: '0'), $start_date);
                $end_time = strtotime((Request::get('end_stunde') ?: '0') . ':' . (Request::get('end_minute') ?: '0'), $start_date);
                if (!($start_time && $end_time && $start_time < $end_time)) {
                    $errors[] = _('Bitte geben Sie korrekte Werte für Start- und Endzeit an!');
                }
            }
            $termin = CourseDate::find(Request::option('singleDateID'));
            if ($termin == null) {
                $termin = new CourseDate();
            }
            $termin->raum     = Request::get('freeRoomText_sd');
            $termin->autor_id = $GLOBALS['user']->id;
            $termin->range_id = $this->course->id;
            $termin->date     = $start_time;
            $termin->end_time = $end_time;
            $termin->date_typ = Request::int('dateType');
            if (!count($errors)) {
                if ($termin->store()) {
                    PageLayout::postSuccess(_('Der Termin wurde geändert.'));
                }
                return $this->relocate('course/dates');
            } else {
                PageLayout::postError(_('Bitte korrigieren Sie Ihre Eingaben:'), $errors);
                $this->date = $termin;
            }
        } elseif ($termin_id) {
            $this->date = new CourseDate($termin_id);
            $xtitle = $this->date->getTypeName() . ': ' . $this->date->getFullname();

        } else {
            $this->date = new CourseDate();
            $xtitle = _('Einzeltermin anlegen');
        }

        PageLayout::setTitle($xtitle);
    }

    /**
     * This method is called to save a singledate for a studygroup.
     *
     * @return void
     */
    public function save_details_action($date_id)
    {
        $this->checkAccess();

        CSRFProtection::verifyUnsafeRequest();

        $termin = CourseDate::find($date_id);
        if ($termin) {
            $termin->date_typ = Request::get('dateType');

            // Assign teachers
            $assigned_teachers = Request::optionArray('assigned_teachers');
            $current_count     = CourseMember::countByCourseAndStatus(
                $termin->course->id,
                'dozent'
            );
            $termin->dozenten = count($assigned_teachers) !== $current_count
                              ? User::findMany($assigned_teachers)
                              : [];

            // Assign groups
            $assigned_groups       = Request::optionArray('assigned_groups');
            $termin->statusgruppen = Statusgruppen::findMany($assigned_groups);

            if ($termin->store()) {
                PageLayout::postSuccess(_('Der Termin wurde geändert.'));
            }
        }
        $this->relocate('course/dates');
    }

    public function new_topic_action()
    {
        Navigation::activateItem('/course/schedule/dates');
        if (Request::isAjax()) {
            PageLayout::setTitle(_("Thema hinzufügen"));
        }

        $this->date   = new CourseDate(Request::option('termin_id'));
        $this->course = Course::findCurrent();
    }

    public function add_topic_action()
    {
        $this->checkAccess();

        if (!Request::get('title')) {
            $this->set_status(400);
            $this->render_json(['message' => _('Geben Sie einen Titel an.')]);
            return;
        }

        $output = ['topic_id' => $topic->id];

        $date = new CourseDate(Request::option('termin_id'));
        $seminar_id = $date->range_id;
        $title      = Request::get('title');
        $topic      = CourseTopic::findByTitle($seminar_id, $title);
        if (!$topic) {
            $topic = new CourseTopic();
            $topic->title       = $title;
            $topic->seminar_id  = $seminar_id;
            $topic->author_id   = $GLOBALS['user']->id;
            $topic->description = '';
            $topic->store();
        }

        if ($date->addTopic($topic)) {
            $factory = $this->get_template_factory();
            $template = $factory->open($this->get_default_template('_topic_li'));
            $template->topic      = $topic;
            $template->date       = $date;
            $template->has_access = $this->hasAccess();
            $template->controller = $this;
            $output['li'] = $template->render();
        }

        $this->render_json($output);
    }

    /**
     * Moves a topic from one date to another.
     * This action will be called from an ajax request and will return only
     * the neccessary output for a single topic element.
     *
     * @param String $topic_id    The id of the topic
     * @param String $old_date_id The id of the original date of the topic
     * @param String $new_date_id The id of the new date of the topic
     * @throws MethodNotAllowedException if request method is not post
     * @throws AccessDeniedException if the user is not allowed to execute the
     *                               action (at least tutor of the course)
     */
    public function move_topic_action($topic_id, $old_date_id, $new_date_id)
    {
        $this->checkAccess();

        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $this->topic = CourseTopic::find($topic_id);
        $this->date = new CourseDate($new_date_id);

        $this->topic->dates->unsetByPK($old_date_id);
        if (!$this->topic->dates->findOneBy('termin_id', $new_date_id)) {
            $this->topic->dates[] = CourseDate::find($new_date_id);
        }
        $this->topic->store();

        $this->set_content_type('text/html;charset=utf-8');
        $this->render_template('course/dates/_topic_li.php');
    }

    public function remove_topic_action()
    {
        $this->checkAccess();

        $topic = new CourseTopic(Request::option('issue_id'));
        $date = new CourseDate(Request::option('termin_id'));
        $date->removeTopic($topic);

        $output = [];
        $this->render_json($output);
    }

    public function export_action()
    {
        $sem = new Seminar($this->course);
        $themen =& $sem->getIssues();

        $termine = getAllSortedSingleDates($sem);

        $dates = [];

        if (is_array($termine) && sizeof($termine) > 0) {
            foreach ($termine as $singledate_id => $singledate) {
                if (!$singledate->isExTermin()) {
                    $tmp_ids = $singledate->getIssueIDs();
                    $title = $description = '';
                    if (is_array($tmp_ids)) {
                        $title = trim(join("\n", array_map(function ($tid) use ($themen) {return $themen[$tid]->getTitle();}, $tmp_ids)));
                        $description = trim(join("\n\n", array_map(function ($tid) use ($themen) {return $themen[$tid]->getDescription();}, $tmp_ids)));
                    }

                    $dates[] = [
                        'date'  => $singledate->toString(),
                        'title' => $title,
                        'description' => $description,
                        'start' => $singledate->getStartTime(),
                        'related_persons' => $singledate->getRelatedPersons(),
                        'groups' => $singledate->getRelatedGroups(),
                        'room' => $singledate->getRoom() ?: $singledate->raum,
                        'type' => $GLOBALS['TERMIN_TYP'][$singledate->getDateType()]['name']
                    ];
                } elseif ($singledate->getComment()) {
                    $dates[] = [
                        'date'  => $singledate->toString(),
                        'title' => _('fällt aus') . ' (' . _('Kommentar:') . ' ' . $singledate->getComment() . ')',
                        'description' => '',
                        'start' => $singledate->getStartTime(),
                        'related_persons' => [],
                        'groups' => [],
                        'room' => '',
                        'type' => $GLOBALS['TERMIN_TYP'][$singledate->getDateType()]['name']
                    ];
                }
            }
        }

        $factory = $this->get_template_factory();
        $template = $factory->open($this->get_default_template('export'));

        $template->set_attribute('dates', $dates);
        $template->lecturer_count = $this->course->countMembersWithStatus('dozent');
        $template->group_count = count($this->course->statusgruppen);
        $content = $template->render();

        $content = mb_encode_numericentity($content, [0x80, 0xffff, 0, 0xffff], 'utf-8');
        $filename = FileManager::cleanFileName($this->course['name'] . '-' . _('Ablaufplan') . '.doc');

        $this->set_content_type(get_mime_type($filename));
        $this->response->add_header('Content-Length', strlen($content));
        $this->response->add_header('Content-Disposition', 'attachment; ' . encode_header_parameter('filename', $filename));
        $this->response->add_header('Expires', 0);
        $this->response->add_header('Cache-Control', 'private');
        $this->response->add_header('Pragma', 'cache');
        $this->render_text($content);
    }

    /**
     * Export list of course dates into CSV format.
     */
    public function export_csv_action()
    {
        $sem = new Seminar($this->course);
        $dates = getAllSortedSingleDates($sem);
        $issues = $sem->getIssues();

        $columns = [
            _('Wochentag'),
            _('Termin'),
            _('Beginn'),
            _('Ende'),
            _('Typ'),
            _('Thema'),
            _('Beschreibung'),
            _('Lehrende'),
            _('Gruppen'),
            _('Raum'),
            _('Raumbeschreibung'),
            _('Sitzplätze')
        ];

        $data = [$columns];

        foreach ($dates as $date) {
            // FIXME this should not be necessary, see https://develop.studip.de/trac/ticket/8101
            if ($date->isExTermin() && $date->getComment() == '') {
                continue;
            }

            $row = [];
            $row[] = strftime('%A', $date->date);
            $row[] = strftime('%x', $date->date);
            $row[] = strftime('%H:%M', $date->date);
            $row[] = strftime('%H:%M', $date->end_time);
            $row[] = $date->getTypeName();

            if ($date->isExTermin()) {
                $row[] = $date->getComment();
                $row[] = '';
            } else {
                $issue = $descr = '';

                foreach ((array) $date->getIssueIDs() as $id) {
                    $issue .= $issues[$id]->getTitle() . "\n";
                    $descr .= kill_format($issues[$id]->getDescription()) . "\n";
                }

                $row[] = trim($issue);
                $row[] = trim($descr);
            }

            $related_persons = '';

            if ($date->related_persons) {
                foreach ($date->related_persons as $user_id) {
                    $related_persons .= User::find($user_id)->getFullname() . "\n";
                }
            }

            $row[] = trim($related_persons);

            $related_groups = '';

            if ($date->related_groups) {
                foreach ($date->related_groups as $group_id) {
                    $related_groups .= Statusgruppen::find($group_id)->name . "\n";
                }
            }

            $row[] = trim($related_groups);

            if ($date->resource_id) {
                $resource_object = ResourceObject::Factory($date->resource_id);
                $row[] = $resource_object->getName();
                $row[] = $resource_object->getDescription();
                $row[] = $resource_object->getSeats();
            } else {
                $row[] = $date->raum;
                $row[] = '';
                $row[] = '';
            }

            $data[] = $row;
        }

        $filename = $sem->name . '-' . _('Ablaufplan') . '.csv';
        $this->render_csv($data, $filename);
    }

    private function hasAccess()
    {
        return $GLOBALS['perm']->have_studip_perm('tutor', $this->course->id);
    }

    private function checkAccess()
    {
        if (!$this->hasAccess()) {
            throw new AccessDeniedException();
        }
    }

    private function assignLockRulesToTemplate()
    {
        $this->cancelled_dates_locked = LockRules::Check(
            $this->course->id,
            'cancelled_dates'
        );
        $this->metadata_locked = LockRules::Check(
            $this->course->id,
            'edit_dates_in_schedule'
        );
        $this->dates_locked = LockRules::Check(
            $this->course->id,
            'room_time'
        );
    }

    public function create_folders_action()
    {
        $this->checkAccess();
        $this->dates = CourseDate::findByRange_id($this->course->id);
        $this->show_confirmation_button = false;
        $this->course_date_folders = [];
        $this->previously_selected_dates = [];
        $confirmed = false;
        if (Request::submitted('go') || Request::submitted('force_go')) {
            CSRFProtection::verifyUnsafeRequest();
            $confirmed = Request::submitted('force_go');
            $this->course_date_folders = Request::optionArray('course_date_folders');
            $this->previously_selected_dates = Request::getArray('previously_selected_dates');
            $count = 0;
            $root_folder_id = Folder::findTopFolder($this->course->id)->getId();
            $new_folders = [];
            $dates_with_folders = [];
            foreach ($this->course_date_folders as $termin_id) {
                //Check if there are already course date folders
                //for the course date:
                $course_date = CourseDate::find($termin_id);
                if ($course_date instanceof CourseDate) {
                    $date_has_folders = count($course_date->folders) > 0;
                    if ($date_has_folders) {
                        $dates_with_folders[] = $course_date;
                    }
                }
                $folder = Folder::build([
                    'range_id'    => $this->course->id,
                    'parent_id'   => $root_folder_id,
                    'range_type'  => 'course',
                    'folder_type' => 'CourseTopicFolder',
                    'user_id'     => $GLOBALS['user']->id
                ]);
                $date_folder = new CourseDateFolder($folder);
                $ok = $date_folder->setDataFromEditTemplate([
                    'course_date_folder_termin_id'  => $termin_id,
                    'course_date_folder_perm_write' => Request::get('course_date_folder_perm_write')
                ]);
                $new_folders[] = $ok;
            }
            //Check if the course_date_folders array
            //and the previously_selected_dates array are equal.
            //If not, then someone has changed the date selection in the
            //confirmation step and the confirmation has to be done again.
            if ($this->previously_selected_dates) {
                //We are definetly confirming the creation of folders.
                if ($this->previously_selected_dates != $this->course_date_folders) {
                    //Confirm again.
                    $confirmed = false;
                }
            }
            if ($dates_with_folders && !$confirmed) {
                $this->previously_selected_dates = $this->course_date_folders;
                if (count($dates_with_folders) == 1) {
                    PageLayout::postWarning(
                        sprintf(
                            _('Für den Termin am %s existiert bereits ein Sitzungs-Ordner. Möchten Sie trotzdem einen weiteren Sitzungs-Ordner erstellen?'),
                            $dates_with_folders[0]->getFullname()
                        )
                    );
                } else {
                    $dates_string = [];
                    foreach ($dates_with_folders as $date) {
                        $dates_string[] = $date->getFullname();
                    }
                    PageLayout::postWarning(
                        _('Für die folgenden Termine gibt es bereits Sitzungs-Ordner. Möchten Sie trotzdem weitere Sitzungs-Ordner erstellen?'),
                        $dates_string
                    );
                }
                $this->show_confirmation_button = true;
                return;
            }

            foreach ($new_folders as $new_folder) {
                if ($new_folder instanceof CourseDateFolder) {
                    $count += $new_folder->store();
                }
            }
            PageLayout::postSuccess(sprintf(_('Es wurden %u Ordner erstellt.'), $count));
            $this->response->add_header('X-Dialog-Close', '1');
            $this->relocate($this->url_for('/index'));
        }
    }
}
