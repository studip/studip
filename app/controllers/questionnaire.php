<?php

require_once 'lib/classes/QuestionType.interface.php';

class QuestionnaireController extends AuthenticatedController
{
    protected $allow_nobody = true; //nobody is allowed

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if ($action != 'courseoverview' && Navigation::hasItem("/tools/questionnaire")) {
            Navigation::activateItem("/tools/questionnaire");
        }
        Sidebar::Get()->setImage(Assets::image_path("sidebar/evaluation-sidebar.png"));
        PageLayout::setTitle(_("Fragebögen"));
        //trigger autoloading:
        class_exists("Vote");
        class_exists("Test");
        class_exists("Freetext");
    }

    public function overview_action()
    {
        if (Navigation::hasItem('/tools/questionnaire/overview')) {
            Navigation::activateItem('/tools/questionnaire/overview');
        }

        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException("Only for logged in users.");
        }
        $this->questionnaires = Questionnaire::findBySQL("user_id = ? ORDER BY mkdate DESC", [$GLOBALS['user']->id]);
        foreach ($this->questionnaires as $questionnaire) {
            if (!$questionnaire['visible'] && $questionnaire->isRunning()) {
                $questionnaire->start();
            }
            if ($questionnaire['visible'] && $questionnaire->isStopped()) {
                $questionnaire->stop();
            }
        }
    }

    public function courseoverview_action()
    {
        $this->range_type = Course::findCurrent() ? 'course' : 'institute';
        if (($this->range_type === "institute") && $GLOBALS['perm']->have_perm('admin')) {
            if (!$GLOBALS['SessionSeminar']) {
                Navigation::activateItem('/admin/institute/questionnaires');
            }
            require_once 'lib/admin_search.inc.php';
        }
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $GLOBALS['SessionSeminar'])) {
            throw new AccessDeniedException("Only for logged in users.");
        }
        Navigation::activateItem("/course/admin/questionnaires");
        $this->questionnaires = Questionnaire::findBySQL("INNER JOIN questionnaire_assignments USING (questionnaire_id) WHERE questionnaire_assignments.range_id = ? AND questionnaire_assignments.range_type = ? ORDER BY questionnaires.mkdate DESC", [$GLOBALS['SessionSeminar'], $this->range_type]);
        foreach ($this->questionnaires as $questionnaire) {
            if (!$questionnaire['visible'] && $questionnaire->isRunning()) {
                $questionnaire->start();
            }
            if ($questionnaire['visible'] && $questionnaire->isStopped()) {
                $questionnaire->stop();
            }
        }
        $this->render_action("overview");
    }

    public function thank_you_action()
    {

    }

    public function edit_action($questionnaire_id = null)
    {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException("Only for authors.");
        }
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if ($this->questionnaire->isNew()) {
            PageLayout::setTitle(_("Neuer Fragebogen"));
        } else {
            PageLayout::setTitle(_("Fragebogen bearbeiten: ").$this->questionnaire['title']);
        }
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Fragebogen ist nicht bearbeitbar.");
        }
        if ($this->questionnaire->isRunning() && $this->questionnaire->countAnswers() > 0) {
            $this->render_text(MessageBox::error(_("Fragebogen ist gestartet worden und kann jetzt nicht mehr bearbeitet werden. Stoppen oder löschen Sie den Fragebogen stattdessen.")));
            return;
        }
        if (Request::isPost()) {
            $order = array_flip(json_decode(Request::get("order"), true));
            $questionnaire_data = Request::getArray("questionnaire");
            $questionnaire_data['startdate'] = $questionnaire_data['startdate']
                ? (strtotime($questionnaire_data['startdate']) ?: time())
                : null;
            $questionnaire_data['stopdate'] = strtotime($questionnaire_data['stopdate']) ?: null;
            $questionnaire_data['copyable'] = (int) $questionnaire_data['copyable'];
            $questionnaire_data['anonymous'] = (int) $questionnaire_data['anonymous'];
            $questionnaire_data['editanswers'] = $questionnaire_data['anonymous'] ? 0 : (int) $questionnaire_data['editanswers'];
            if ($this->questionnaire->isNew()) {
                $questionnaire_data['visible'] = ($questionnaire_data['startdate'] <= time() && (!$questionnaire_data['stopdate'] || $questionnaire_data['stopdate'] >= time())) ? 1 : 0;
            }
            $this->questionnaire->setData($questionnaire_data);
            $question_types_data = Request::getArray("question_types");
            foreach ($question_types_data as $question_id => $question_type) {
                $question = null;
                foreach ($this->questionnaire->questions as $index => $q) {
                    if ($q->getId() === $question_id) {
                        $question = $q;
                        break;
                    }
                }
                if (!$question) {
                    $question = new $question_type($question_id);
                    $this->questionnaire->questions[] = $question;
                }
                $question['position'] = $order[$question_id] + 1;
                $question->createDataFromRequest();
            }
            foreach ($this->questionnaire->questions as $q) {
                if (!in_array($q->getId(), array_keys($question_types_data))) {
                    $q->delete();
                }
            }
            if (Request::submitted("questionnaire_store")) {
                //save everything
                $is_new = $this->questionnaire->isNew();
                if ($is_new) {
                    $this->questionnaire['user_id'] = $GLOBALS['user']->id;
                }
                $this->questionnaire->store();

                if ($is_new && Request::get("range_id") && Request::get("range_type")) {
                    if (Request::get("range_id") === "start" && !$GLOBALS['perm']->have_perm("root")) {
                        throw new Exception("Der Fragebogen darf nicht von Ihnen auf die Startseite eingehängt werden, sondern nur von einem Admin.");
                    }
                    if (Request::get("range_type") === "course" && !$GLOBALS['perm']->have_studip_perm("tutor", Request::get("range_id"))) {
                        throw new Exception("Der Fragebogen darf nicht in die ausgewählte Veranstaltung eingebunden werden.");
                    }
                    if (Request::get("range_type") === "user" && Request::get("range_id") !== $GLOBALS['user']->id) {
                        throw new Exception("Der Fragebogen darf nicht in diesen Bereich eingebunden werden.");
                    }
                    $assignment = new QuestionnaireAssignment();
                    $assignment['questionnaire_id'] = $this->questionnaire->getId();
                    $assignment['range_id'] = Request::option("range_id");
                    $assignment['range_type'] = Request::get("range_type");
                    $assignment['user_id'] = $GLOBALS['user']->id;
                    $assignment->store();
                }
                if ($is_new) {
                    $message = MessageBox::success(_("Der Fragebogen wurde erfolgreich erstellt."));
                } else {
                    $message = MessageBox::success(_("Der Fragebogen wurde gespeichert."));
                }
                if (Request::isAjax()) {
                    $this->questionnaire->restore();
                    $this->questionnaire->resetRelation("assignments");
                    $output = [
                            'questionnaire_id' => $this->questionnaire->getId(),
                            'overview_html' => $this->render_template_as_string("questionnaire/_overview_questionnaire.php"),
                            'widget_html' => $this->questionnaire->isStarted()
                                ? $this->render_template_as_string("questionnaire/_widget_questionnaire.php")
                                : "",
                            'message' => $message->__toString()
                    ];
                    $this->response->add_header("X-Dialog-Close", 1);
                    $this->response->add_header("X-Dialog-Execute", "STUDIP.Questionnaire.updateOverviewQuestionnaire");
                    $this->render_json($output);
                } else {
                    PageLayout::postMessage($message);
                    if (Request::get("range_type") === "user") {
                        $this->redirect("profile");
                    } elseif (Request::get("range_type") === "course") {
                        $this->redirect("course/overview");
                    } elseif (Request::get("range_id") === "start") {
                        $this->redirect("start");
                    } else {
                        $this->redirect("questionnaire/overview");
                    }
                }
            }
            return;
        }

        $statement = DBManager::get()->prepare("
            SELECT question_id 
            FROM questionnaire_questions
            WHERE questionnaire_questions.questionnaire_id = ?
            ORDER BY position ASC
        ");
        $statement->execute(array($this->questionnaire->getId()));
        $this->order = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function copy_action($from)
    {
        $this->old_questionnaire = Questionnaire::find($from);
        if (!$this->old_questionnaire->isCopyable()) {
            throw new AccessDeniedException("Reproduction and copy forbidden");
        }
        $this->questionnaire = new Questionnaire();
        $this->questionnaire->setData($this->old_questionnaire->toArray());
        $this->questionnaire->setId($this->questionnaire->getNewId());
        $this->questionnaire['user_id'] = $GLOBALS['user']->id;
        $this->questionnaire['startdate'] = null;
        $this->questionnaire['stopdate'] = null;
        $this->questionnaire['mkdate'] = time();
        $this->questionnaire['chdate'] = time();
        $this->questionnaire->store();
        foreach ($this->old_questionnaire->questions as $question) {
            $new_question = QuestionnaireQuestion::build($question->toArray());
            $new_question->setId($new_question->getNewId());
            $new_question['questionnaire_id'] = $this->questionnaire->getid();
            $new_question['mkdate'] = time();
            $new_question->store();
        }
        PageLayout::postMessage(MessageBox::success(_("Der Fragebogen wurde kopiert. Wo soll er angezeigt werden?")));
        $this->redirect("questionnaire/context/".$this->questionnaire->getId());
    }

    public function delete_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht bearbeitbar.");
        }
        $this->questionnaire->delete();
        PageLayout::postMessage(MessageBox::success(_("Der Fragebogen wurde gelöscht.")));
        if (Request::get("redirect")) {
            $this->redirect(Request::get("redirect"));
        } else {
            $this->redirect("questionnaire/overview");
        }
    }

    public function bulkdelete_action()
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }
        foreach (Request::getArray("q") as $questionnaire_id) {
            $questionnaire = new Questionnaire($questionnaire_id);
            if ($questionnaire->isEditable()) {
                $questionnaire->delete();
            }
        }
        PageLayout::postSuccess(_("Fragebögen wurden gelöscht."));
        if (Request::get("range_type") === "user") {
            $this->redirect("questionnaire/overview");
        } elseif (Request::get("range_type") === "course") {
            $this->redirect("questionnaire/courseoverview");
        } elseif (Request::get("range_id") === "start") {
            $this->redirect("start");
        } else {
            $this->redirect("questionnaire/overview");
        }
    }

    public function add_question_action()
    {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException("Only for logged in users.");
        }
        $class = Request::get("questiontype");
        $this->question = new $class();
        $this->question->setId($this->question->getNewId());

        $template = $this->get_template_factory()->open("questionnaire/_question.php");
        $template->set_attribute("question", $this->question);

        $output = [
            'html' => $template->render(),
            'question_id' => $this->question->getId()
        ];
        $this->render_json($output);
    }

    public function answer_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isViewable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht einsehbar.");
        }
        object_set_visit($questionnaire_id, 'vote');
        if (Request::isPost()) {
            $answered_before = $this->questionnaire->isAnswered();
            if ($this->questionnaire->isAnswerable()) {
                foreach ($this->questionnaire->questions as $question) {
                    $answer = $question->createAnswer();
                    if (!$answer['question_id']) {
                        $answer['question_id'] = $question->getId();
                    }
                    $answer['user_id'] = $GLOBALS['user']->id;
                    if (!$answer['answerdata']) {
                        $answer['answerdata'] = [];
                    }
                    if ($this->questionnaire['anonymous']) {
                        $answer['user_id'] = 'anonymous';
                        $answer['chdate'] = 1;
                        $answer['mkdate'] = 1;
                        $this->anonAnswers[] = $answer->toArray();
                        $answer['user_id'] = null;
                    }
                    $answer->store();
                }
                if ($this->questionnaire['anonymous'] && ($GLOBALS['user']->id !== "nobody")) {
                    $anonymous_answer = new QuestionnaireAnonymousAnswer();
                    $anonymous_answer['questionnaire_id'] = $this->questionnaire->getId();
                    $anonymous_answer['user_id'] = $GLOBALS['user']->id;
                    $anonymous_answer->store();
                }
                if (!$answered_before && !$this->questionnaire['anonymous'] && ($this->questionnaire['user_id'] !== $GLOBALS['user']->id)) {
                    $url = URLHelper::getURL("dispatch.php/questionnaire/evaluate/" . $this->questionnaire->getId(), [], true);
                    PersonalNotifications::add(
                        $this->questionnaire['user_id'],
                        $url,
                        sprintf(_("%s hat an der Befragung '%s' teilgenommen."), get_fullname(), $this->questionnaire['title']),
                        "questionnaire_" . $this->questionnaire->getId(),
                        Icon::create('vote', 'clickable'),
                        true
                    );
                }
            }

            if (Request::isAjax()) {
                $this->response->add_header("X-Dialog-Close", "1");
                $this->response->add_header("X-Dialog-Execute", "STUDIP.Questionnaire.updateWidgetQuestionnaire");
                $this->render_template("questionnaire/evaluate");
            } elseif (Request::get("range_type") === "user") {
                PageLayout::postMessage(MessageBox::success(_("Danke für die Teilnahme!")));
                $this->redirect("profile?username=".get_username(Request::option("range_id")));
            } elseif (Request::get("range_type") === "course") {
                PageLayout::postMessage(MessageBox::success(_("Danke für die Teilnahme!")));
                $this->redirect("course/overview?cid=".Request::option("range_id"));
            } elseif (Request::get("range_id") === "start") {
                PageLayout::postMessage(MessageBox::success(_("Danke für die Teilnahme!")));
                $this->redirect("start");
            } else {
                PageLayout::postMessage(MessageBox::success(_("Danke für die Teilnahme!")));
                if ($GLOBALS['perm']->have_perm("autor")) {
                    $this->redirect("questionnaire/overview");
                } else {
                    $this->redirect("questionnaire/thank_you");
                }
            }
        }
        $this->range_type = Request::get("range_type");
        $this->range_id = Request::get("range_id");
        PageLayout::setTitle(sprintf(_("Fragebogen beantworten: %s"), $this->questionnaire->title));
    }

    public function evaluate_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isViewable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht einsehbar.");
        }
        object_set_visit($questionnaire_id, 'vote');
        PageLayout::setTitle(sprintf(_("Fragebogen: %s"), $this->questionnaire->title));

        if (Request::isAjax() && !$_SERVER['HTTP_X_DIALOG']) {
            //Wenn das hier direkt auf der Übersichts-/Profil-/Startseite angezeigt
            //wird, brauchen wir kein 'Danke für die Teilnahme'.
            PageLayout::clearMessages();
        }
    }

    public function stop_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht bearbeitbar.");
        }
        $this->questionnaire->stop();

        PageLayout::postMessage(MessageBox::success(_("Die Befragung wurde beendet.")));
        if (Request::get("redirect")) {
            $this->redirect(Request::get("redirect"));
        } else {
            $this->redirect("questionnaire/overview");
        }
    }

    public function start_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht bearbeitbar.");
        }
        $this->questionnaire->start();

        PageLayout::postMessage(MessageBox::success(_("Die Befragung wurde gestartet.")));
        if (Request::get("redirect")) {
            $this->redirect(Request::get("redirect"));
        } else {
            $this->redirect("questionnaire/overview");
        }
    }

    public function export_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht exportierbar.");
        }
        $csv = [[_("Nummer"), _("Benutzername"), _("Nachname"), _("Vorname"), _("E-Mail")]];

        $results = [];
        $user_ids = [];

        foreach ($this->questionnaire->questions as $question) {
            $result = (array) $question->getResultArray();
            foreach ($result as $frage => $r) {
                $csv[0][] = $frage;
                $user_ids = array_merge($user_ids, array_keys($r));
                $user_ids = array_unique($user_ids);
            }
            $results[] = $result;
        }

        foreach ($user_ids as $key => $user_id) {
            $user = User::find($user_id);
            if ($user) {
                $csv_line = [$key + 1, $user['username'], $user['Nachname'], $user['Vorname'], $user['Email']];
            } else {
                $csv_line = [$key + 1, $user_id, '', '', ''];
            }

            foreach ($results as $result) {
                foreach ($result as $frage => $value) {
                    $csv_line[] = $value[$user_id];
                }
            }
            $csv[] = $csv_line;
        }
        $this->response->add_header('Content-Type', "text/csv");
        $this->response->add_header('Content-Disposition', "attachment; " . encode_header_parameter('filename', $this->questionnaire['title'].'.csv'));
        $this->render_text(array_to_csv($csv));
    }

    public function context_action($questionnaire_id)
    {
        $this->questionnaire = new Questionnaire($questionnaire_id);
        if (!$this->questionnaire->isEditable()) {
            throw new AccessDeniedException("Der Fragebogen ist nicht bearbeitbar.");
        }
        foreach ($this->questionnaire->assignments as $relation) {
            if ($relation['range_type'] === "user") {
                $this->profile = $relation;
            }
            if ($relation['range_id'] === "public") {
                $this->public = $relation;
            }
            if ($relation['range_id'] === "start") {
                $this->start = $relation;
            }
        }
        if (Request::isPost()) {
            if (Request::get("user")) {
                if (!$this->profile) {
                    $this->profile = new QuestionnaireAssignment();
                    $this->profile['questionnaire_id'] = $this->questionnaire->getId();
                    $this->profile['range_id'] = $GLOBALS['user']->id;
                    $this->profile['range_type'] = "user";
                    $this->profile['user_id'] = $GLOBALS['user']->id;
                    $this->profile->store();
                }
            } else {
                if ($this->profile) {
                    $this->profile->delete();
                }
            }
            if (Request::get("public")) {
                if (!$this->public) {
                    $this->public = new QuestionnaireAssignment();
                    $this->public['questionnaire_id'] = $this->questionnaire->getId();
                    $this->public['range_id'] = "public";
                    $this->public['range_type'] = "static";
                    $this->public['user_id'] = $GLOBALS['user']->id;
                    $this->public->store();
                }
            } else {
                if ($this->public) {
                    $this->public->delete();
                }
            }
            if ($GLOBALS['perm']->have_perm("root")) {
                if (Request::get("start")) {
                    if (!$this->start) {
                        $this->start = new QuestionnaireAssignment();
                        $this->start['questionnaire_id'] = $this->questionnaire->getId();
                        $this->start['range_id'] = "start";
                        $this->start['range_type'] = "static";
                        $this->start['user_id'] = $GLOBALS['user']->id;
                        $this->start->store();
                    }
                } else {
                    if ($this->start) {
                        $this->start->delete();
                    }
                }
            }
            if (Request::option("add_seminar_id") && $GLOBALS['perm']->have_studip_perm("tutor", Request::option("add_seminar_id"))) {
                $course_assignment = new QuestionnaireAssignment();
                $course_assignment['questionnaire_id'] = $this->questionnaire->getId();
                $course_assignment['range_id'] = Request::option("add_seminar_id");
                $course_assignment['range_type'] = "course";
                $course_assignment['user_id'] = $GLOBALS['user']->id;
                $course_assignment->store();
            }
            if (Request::option("add_institut_id") && $GLOBALS['perm']->have_studip_perm("admin", Request::option("add_institut_id"))) {
                $course_assignment = new QuestionnaireAssignment();
                $course_assignment['questionnaire_id'] = $this->questionnaire->getId();
                $course_assignment['range_id'] = Request::option("add_institut_id");
                $course_assignment['range_type'] = "institute";
                $course_assignment['user_id'] = $GLOBALS['user']->id;
                $course_assignment->store();
            }

            foreach (Request::getArray("remove_sem") as $seminar_id) {
                if ($GLOBALS['perm']->have_studip_perm("tutor", $seminar_id)) {
                    $course_assignment = QuestionnaireAssignment::findBySeminarAndQuestionnaire($seminar_id, $this->questionnaire->getId());
                    $course_assignment->delete();
                }
            }

            PageLayout::postMessage(MessageBox::success(_("Die Bereichszuweisungen wurden gespeichert.")));
            $this->questionnaire->restore();
            $this->questionnaire->resetRelation("assignments");
            $output = [
                'func' => "STUDIP.Questionnaire.updateOverviewQuestionnaire",
                'payload' => [
                    'questionnaire_id' => $this->questionnaire->getId(),
                    'html' => $this->render_template_as_string("questionnaire/_overview_questionnaire.php")
                ]
            ];
            $this->response->add_header("X-Dialog-Execute", json_encode($output));
        }
        PageLayout::setTitle(sprintf(_("Bereiche für Fragebogen: %s"), $this->questionnaire->title));
    }

    public function widget_action($range_id, $range_type = "course")
    {
        if (get_class($this->parent_controller) === __CLASS__) {
            throw new RuntimeException('widget_action must be relayed');
        }
        $this->range_id = $range_id;
        $this->range_type = $range_type;
        if (in_array($this->range_id, ["public", "start"])) {
            $this->range_type = "static";
        }
        $statement = DBManager::get()->prepare("
            SELECT questionnaires.*
            FROM questionnaires
                INNER JOIN questionnaire_assignments ON (questionnaires.questionnaire_id = questionnaire_assignments.questionnaire_id)
            WHERE questionnaire_assignments.range_id = :range_id
                AND questionnaire_assignments.range_type = :range_type
                AND startdate <= UNIX_TIMESTAMP()
            ORDER BY questionnaires.mkdate DESC
        ");
        $statement->execute([
            'range_id' => $this->range_id,
            'range_type' => $this->range_type
        ]);
        $this->questionnaire_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $stopped_visible = 0;
        foreach ($this->questionnaire_data as $i => $questionnaire) {
            $one = Questionnaire::buildExisting($questionnaire);
            if (!$questionnaire['visible'] && $one->isRunning()) {
                $one->start();
            }
            if ($questionnaire['visible'] && $one->isStopped()) {
                $one->stop();
            }
            if ($one->isStopped() && $one->resultsVisible()) {
                $stopped_visible++;
            }
            if ($one->isStopped() && (!$one->resultsVisible() || !Request::get("questionnaire_showall"))) {
                unset($this->questionnaire_data[$i]);
                continue;
            }

            object_set_visit($questionnaire['questionnaire_id'], 'vote');
        }
        if (in_array($this->range_type, ["course", "institute"])
                && !$GLOBALS['perm']->have_studip_perm("tutor", $this->range_id)
                && !($stopped_visible || count($this->questionnaire_data))) {
            $this->render_nothing();
        }
    }


    /**
     * The assign action allows assigning multiple questionnaires
     * to multiple courses.
     */
    public function assign_action()
    {
        PageLayout::setTitle(_('Fragebögen zuordnen'));

        if (Navigation::hasItem('/tools/questionnaire/assign')) {
            Navigation::activateItem('/tools/questionnaire/assign');
        }

        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $user = User::findCurrent();

        $this->available_semesters = Semester::findBySql(
            'TRUE ORDER BY beginn DESC'
        );
        $this->available_institutes = Institute::getMyInstitutes($user->id);
        $this->available_course_types = SemType::getTypes();
        $this->selected_questionnaires = [];

        $this->step = 0;

        //We accept only forms which have been sent via POST!
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if (Request::submitted('search_courses')) {
                $this->step = 1;
            } elseif (Request::submitted('select_courses')) {
                $this->step = 2;
            } elseif (Request::submitted('copy') || Request::submitted('assign')) {
                $this->step = 3;
            }

            if ($this->step >= 1) {
                //Step 1: Search for courses.
                $this->semester_id = Request::get('semester_id');
                $this->institute_id = Request::get('institute_id');
                $this->course_type_id = Request::get('course_type_id');

                if ($this->institute_id) {
                    //Check if the user has at least admin permissions on the selected
                    //institute. If not, then don't process the submitted data
                    //any further:
                    if (!$perm->have_studip_perm('admin', $this->institute_id, $user_id)) {
                        PageLayout::postError(
                            _('Sie haben unzureichende Berechtigungen an der gewählten Einrichtung! Bitte wählen Sie eine andere Einrichtung!')
                        );
                        $this->step = 0;
                        return;
                    }
                }

                $this->semester = Semester::find($this->semester_id);
                if ($this->institute_id) {
                    $this->institute = Institute::find($this->institute_id);
                }

                $this->error_message = '';

                if (!$this->semester) {
                    PageLayout::postError(_('Es wurde kein gültiges Semester ausgewählt!'));
                    $this->step = 0;
                    return;
                }

                //Search courses matching the search criteria:

                $sql = 'start_time = :semester_begin ';
                $sql_array = [
                    'semester_begin' => $this->semester->beginn
                ];

                if ($this->institute) {
                    $sql .= 'AND institut_id = :institute_id ';
                    $sql_array['institute_id'] = $this->institute->id;
                }
                if ($this->course_type_id) {
                    $sql .= 'AND status = :course_type_id ';
                    $sql_array['course_type_id'] = $this->course_type_id;
                }

                $sql .= 'ORDER BY name ASC';

                $courses = Course::findBySql($sql, $sql_array);

                $this->found_courses = [];

                //We can only add those courses where the current user
                //has at least admin permissions:
                foreach ($courses as $course) {
                    if ($GLOBALS['perm']->have_studip_perm('admin', $course->id, $user_id)) {
                        $this->found_courses[] = $course;
                    }
                }
            }

            if ($this->step >= 2) {
                //Step 2: Courses have been selected. Search for questionnaires.
                $this->course_id_list = Request::getArray('course_id_list');
                $this->selected_courses = Course::findMany($this->course_id_list);
                if (!$this->selected_courses) {
                    PageLayout::postError(
                        _('Es wurde keine Veranstaltung ausgewählt! Bitte mindestens eine Veranstaltung auswählen!')
                    );
                    $this->step = 1;
                    return;
                }
                $courses_without_perms = [];
                foreach ($this->selected_courses as $course) {
                    if (!$GLOBALS['perm']->have_studip_perm('admin', $course->id, $user_id)) {
                        $courses_without_perms[] = $course->getFullName();
                    }
                }

                if ($courses_without_perms) {
                    PageLayout::postError(
                        ngettext(
                            'Ihre Berechtigungen reichen nicht, um Fragebögen zu der folgenden Veranstaltung zuweisen zu können:',
                            'Ihre Berechtigungen reichen nicht, um Fragebögen zu den folgenden Veranstaltungen zuweisen zu können:',
                            count($courses_without_perms)
                        ),
                        $courses_without_perms
                    );
                    $this->step = 1;
                    return;
                }

                //Get only the questionnaires of the current user:
                $this->questionnaires = Questionnaire::findBySql(
                    'user_id = :user_id ORDER BY mkdate DESC',
                    [
                        'user_id' => $user->id
                    ]
                );
            }

            if ($this->step >= 3) {
                //Step 3: Questionnaires have been selected. Assign them
                //to the found courses.
                $this->selected_questionnaire_ids = Request::getArray('selected_questionnaire_ids');
                $this->delete_dates = Request::get('delete_dates');
                $this->copy_questionnaires = Request::submitted('copy');

                //Get only the questionnaires of the current user:
                $this->selected_questionnaires = Questionnaire::findBySql(
                    'user_id = :user_id AND questionnaire_id IN ( :questionnaire_ids )',
                    [
                        'user_id' => $user->id,
                        'questionnaire_ids' => $this->selected_questionnaire_ids
                    ]
                );
                if (!$this->selected_questionnaires) {
                    PageLayout::postError(
                        _('Es wurde kein Fragebogen ausgewählt! Bitte mindestens einen Fragebogen auswählen!')
                    );
                    $this->step = 2;
                    return;
                }

                $errors = [];
                foreach ($this->selected_courses as $course) {
                    foreach ($this->selected_questionnaires as $questionnaire) {
                        if ($copy_questionnaires) {
                            //The questionnaire shall be copied and only the copy
                            //shall be placed inside the course.

                            //The following code to copy a questionnaire was copied
                            //from the questionnaire controller's copy_action method.
                            //If that method changes please keep this code "in sync"
                            //with the code from the questionnaire controller to avoid
                            //misbehavior of this plugin.
                            $new_questionnaire = new Questionnaire();
                            $new_questionnaire->setData($questionnaire->toArray());
                            $new_questionnaire->setId($new_questionnaire->getNewId());
                            $new_questionnaire->user_id = $user->id;
                            //Contrary to the original code, copied questionnaires
                            //may still contain the start date and end date of the
                            //original questionnaire.
                            if ($this->delete_dates) {
                                $new_questionnaire->startdate = null;
                                $new_questionnaire->stopdate = null;
                            }

                            $new_questionnaire->mkdate = time();
                            $new_questionnaire->chdate = time();
                            $new_questionnaire->store();
                            foreach ($questionnaire->questions as $question) {
                                $new_question = QuestionnaireQuestion::build($question->toArray());
                                $new_question->setId($new_question->getNewId());
                                $new_question->questionnaire_id = $new_questionnaire->id;
                                $new_question->mkdate = time();
                                $new_question->store();
                            }

                            $assignment = new QuestionnaireAssignment();
                            $assignment->questionnaire_id = $new_questionnaire->id;
                            $assignment->range_id = $course->id;
                            $assignment->range_type = 'course';
                            $assignment->user_id = $user->id;
                            if (!$assignment->store()) {
                                $errors[] = sprintf(
                                    _('Fragebogen "%1$s" konnte nicht in Veranstaltung "%2$s" kopiert werden!'),
                                    $new_questionnaire->title,
                                    $course->name
                                );
                            }
                        } else {
                            //The questionnaire shall be assigned to the course.
                            //We must check if the association already exists.
                            $assignment = QuestionnaireAssignment::findBySeminarAndQuestionnaire(
                                $course->id,
                                $questionnaire->id
                            );

                            if (!$assignment) {
                                //The assignment doesn't exist: create it:
                                $assignment = new QuestionnaireAssignment();
                                $assignment->questionnaire_id = $questionnaire->id;
                                $assignment->range_id = $course->id;
                                $assignment->range_type = 'course';
                                $assignment->user_id = $user->id;
                                if (!$assignment->store()) {
                                    $errors[] = sprintf(
                                        _('Fragebogen "%1$s" konnte nicht zu Veranstaltung "%2$s" zugeordnet werden!'),
                                        $questionnaire->title,
                                        $course->name
                                    );
                                }
                            }
                        }
                    }
                }

                if ($errors) {
                    PageLayout::postError(
                        _('Beim Zuordnen traten Fehler auf:'),
                        $errors
                    );
                } else {
                    PageLayout::postSuccess(
                        _('Alle gewählten Fragebögen wurden den gewählten Veranstaltungen zugeordnet!')
                    );
                }
            }
        }
    }
}
