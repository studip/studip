<?php

class Course_TopicsController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        checkObjectModule("schedule");

        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _("Themen")));

        $seminar = new Seminar(Course::findCurrent());
        $this->forum_activated = $seminar->getSlotModule('forum');
        $this->documents_activated = $seminar->getSlotModule('documents');

        $this->setupSidebar($action);
    }

    public function index_action()
    {
        if (Request::isPost() && Request::get("edit") && $GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
            $topic = new CourseTopic(Request::option("issue_id"));
            if ($topic['seminar_id'] && ($topic['seminar_id'] !== Context::getId())) {
                throw new AccessDeniedException();
            }

            $topic['title']         = Request::get("title");
            $topic['description']   = Studip\Markup::purifyHtml(Request::get("description"));
            $topic['paper_related'] = (bool) Request::int('paper_related');
            if ($topic->isNew()) {
                $topic['seminar_id'] = Context::getId();
            }
            $topic->store();

            //change dates for this topic
            $former_date_ids = $topic->dates->pluck("termin_id");
            $new_date_ids = array_keys(Request::getArray("date"));
            foreach (array_diff($former_date_ids, $new_date_ids) as $delete_termin_id) {
                $topic->dates->unsetByPk($delete_termin_id);
            }
            foreach (array_diff($new_date_ids, $former_date_ids) as $add_termin_id) {
                $date = CourseDate::find($add_termin_id);
                if ($date) {
                    $topic->dates[] = $date;
                }
            }
            $topic->store();

            if (Request::get("folder")) {
                $topic->connectWithDocumentFolder();
            }

            // create a connection to the module forum (can be anything)
            // will update title and description automagically
            if (Request::get("forumthread")) {
                $topic->connectWithForumThread();
            }

            if (Request::option("issue_id") === "new") {
                Request::set("open", $topic->getId());
            }
            PageLayout::postMessage(MessageBox::success(_("Thema gespeichert.")));
            $this->redirect("course/topics/index");
        }

        if (Request::isPost() && Request::option("move_down")) {
            $topics = CourseTopic::findBySeminar_id(Context::getId());
            $mainkey = null;
            foreach ($topics as $key => $topic) {
                if ($topic->getId() === Request::option("move_down")) {
                    $mainkey = $key;
                }
                $topic['priority'] = $key + 1;
            }
            if ($mainkey !== null && $mainkey < count($topics)) {
                $topics[$mainkey]->priority++;
                $topics[$mainkey + 1]->priority--;
            }
            foreach ($topics as $key => $topic) {
                $topic->store();
            }
        }
        if (Request::isPost() && Request::option("move_up")) {
            $topics = CourseTopic::findBySeminar_id(Context::getId());
            foreach ($topics as $key => $topic) {
                if (($topic->getId() === Request::option("move_up")) && $key > 0) {
                    $topic['priority'] = $key;
                    $topics[$key - 1]->priority = $key + 1;
                    $topics[$key - 1]->store();
                } else {
                    $topic['priority'] = $key + 1;
                }
                $topic->store();
            }
        }

        Navigation::activateItem('/course/schedule/topics');
        $this->topics = CourseTopic::findBySeminar_id(Context::getId());
        $this->cancelled_dates_locked = LockRules::Check(Context::getId(), 'cancelled_dates');
    }

    public function delete_action($topic_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
            throw new AccessDeniedException();
        }

        $topic = new CourseTopic($topic_id);

        if ($topic['seminar_id'] && ($topic['seminar_id'] !== Context::getId())) {
            throw new AccessDeniedException();
        }

        $topic->delete();
        PageLayout::postSuccess(_('Thema gelöscht.'));

        $this->redirect('course/topics');
    }

    public function edit_action($topic_id = null)
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
            throw new AccessDeniedException();
        }
        $this->topic = new CourseTopic($topic_id);
        $this->dates = CourseDate::findBySeminar_id(Context::getId());

        if (Request::isXhr()) {
            PageLayout::setTitle($topic_id ? sprintf(_('Bearbeiten: %s'), $this->topic['title']) : _("Neues Thema erstellen"));
        } else {
            Navigation::activateItem('/course/schedule/topics');
        }
    }

    public function allow_public_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
            throw new AccessDeniedException();
        }
        $config = CourseConfig::get(Context::getId());
        $config->store('COURSE_PUBLIC_TOPICS', !$config->COURSE_PUBLIC_TOPICS);
        $this->redirect("course/topics");
    }

    public function copy_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::getId())) {
            throw new AccessDeniedException();
        }
        if (Request::submitted("copy")) {
            $prio = 1;
            foreach (Course::find(Context::getId())->topics as $topic) {
                $prio = max($prio, $topic['priority']);
            }
            foreach (Request::getArray("topic") as $topic_id => $value) {
                $topic = new CourseTopic($topic_id);
                $topic = clone $topic;
                $topic['seminar_id'] = Context::getId();
                $topic['priority'] = $prio;
                $prio++;
                $topic->setId($topic->getNewId());
                $topic->setNew(true);
                $topic->store();
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%s Themen kopiert."), count(Request::getArray("topic")))));
            $this->redirect("course/topics");
        }
        if ($GLOBALS['perm']->have_perm("root")) {
            $this->courseSearch = new SQLSearch("
                SELECT seminare.Seminar_id, CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.name, '(', IF(seminare.duration_time = 0, semester_data.name, 'unbegrenzt'), ') (', COUNT(issue_id), ')')
                FROM seminare
                INNER JOIN semester_data ON (semester_data.beginn = seminare.start_time)
                INNER JOIN themen ON themen.seminar_id = seminare.Seminar_id
                WHERE seminare.VeranstaltungsNummer LIKE :input OR seminare.name LIKE :input
                GROUP BY seminare.Seminar_id
                ORDER BY semester_data.beginn DESC, seminare.VeranstaltungsNummer ASC, seminare.name ASC
                ",
                _("Veranstaltung suchen"),
                "seminar_id"
            );
        } elseif ($GLOBALS['perm']->have_perm("admin")) {
            $this->courseSearch = new SQLSearch("
                SELECT seminare.Seminar_id, CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.name, '(', IF(seminare.duration_time = 0, semester_data.name, 'unbegrenzt'), ') (', COUNT(issue_id), ')')
                FROM seminare
                    INNER JOIN seminar_inst ON (seminare.Seminar_id = seminar_inst.seminar_id)
                    INNER JOIN user_inst ON (user_inst.Institut_id = seminar_inst.institut_id)
                    INNER JOIN semester_data ON (semester_data.beginn = seminare.start_time)
                    INNER JOIN themen ON themen.seminar_id = seminare.Seminar_id

                WHERE seminare.VeranstaltungsNummer LIKE :input OR seminare.name LIKE :input
                    AND user_inst.user_id = ".DBManager::get()->quote($GLOBALS['user']->id)."
                    AND user_inst.inst_perms = 'admin'
                GROUP BY seminare.Seminar_id
                ORDER BY semester_data.beginn DESC, seminare.VeranstaltungsNummer ASC, seminare.name ASC
                ",
                _("Veranstaltung suchen"),
                "seminar_id"
            );
        } else {
            $this->courseSearch = new SQLSearch("
                SELECT seminare.Seminar_id, CONCAT_WS(' ', seminare.VeranstaltungsNummer, seminare.name, '(', IF(seminare.duration_time = 0, semester_data.name, 'unbegrenzt'), ') (', COUNT(issue_id), ')')
                FROM seminare
                    INNER JOIN seminar_user ON (seminare.Seminar_id = seminar_user.Seminar_id)
                    INNER JOIN semester_data ON (semester_data.beginn = seminare.start_time)
                    INNER JOIN themen ON themen.seminar_id = seminare.Seminar_id
                WHERE seminare.VeranstaltungsNummer LIKE :input OR seminare.name LIKE :input
                    AND seminar_user.status IN ('tutor', 'dozent')
                    AND seminar_user.user_id = ".DBManager::get()->quote($GLOBALS['user']->id)."
                GROUP BY seminare.Seminar_id
                ORDER BY semester_data.beginn DESC, seminare.VeranstaltungsNummer ASC, seminare.name ASC
                ",
                _("Veranstaltung suchen"),
                "seminar_id"
            );
        }
        PageLayout::setTitle(_("Themen aus Veranstaltung kopieren"));
    }

    public function fetch_topics_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Request::option("seminar_id"))) {
            throw new AccessDeniedException();
        }
        $this->topics = CourseTopic::findBySeminar_id(Request::option("seminar_id"));
        $output = [
            'html' => $this->render_template_as_string("course/topics/_topiclist.php")
        ];
        $this->render_json($output);
    }

    private function setupSidebar($action)
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/date-sidebar.png');

        $actions = $sidebar->addWidget(new ActionsWidget());
        if ($action === 'index') {
            $actions->addLink(
                _('Alle Themen aufklappen'),
                $this->url_for('course/topics/show'),
                Icon::create('arr_1down'),
                ['onclick' => "jQuery('table.withdetails > tbody > tr:not(.details):not(.open) > :first-child a').click(); return false;"]
            );
            $actions->addLink(
                _('Alle Themen zuklappen'),
                $this->url_for('course/topics/hide'),
                Icon::create('arr_1right'),
                ['onclick' => "jQuery('table.withdetails > tbody > tr:not(.details).open > :first-child a').click(); return false;"]
            );
        }
        if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
            $actions->addLink(
                _('Neues Thema erstellen'),
                $this->url_for('course/topics/edit'),
                Icon::create('add')
            )->asDialog();
            $actions->addLink(
                _('Themen aus Veranstaltung kopieren'),
                $this->url_for('course/topics/copy'),
                Icon::create('topic+add')
            )->asDialog();
        }

        if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
            $options = $sidebar->addWidget(new OptionsWidget());
            $options->addCheckbox(
                _("Themen öffentlich einsehbar"),
                CourseConfig::get(Context::getId())->COURSE_PUBLIC_TOPICS,
                $this->url_for('course/topics/allow_public')
            );
        }
    }
}
