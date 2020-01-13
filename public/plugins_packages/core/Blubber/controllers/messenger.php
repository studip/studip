<?php
class MessengerController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->assets_url = $this->plugin->getPluginURL() . '/assets/';

        PageLayout::setHelpKeyword("Basis/InteraktionBlubber");
    }

    public function course_action($thread_id = null)
    {
        PageLayout::setTitle(_("Blubber"));

        Navigation::activateItem("/course/blubber");


        $this->threads = BlubberThread::findBySeminar(Context::get()->id, true);

        if (!$thread_id) {
            $thread_id = $GLOBALS['user']->cfg->BLUBBER_DEFAULT_THREAD;
        }
        if ($thread_id) {
            foreach ($this->threads as $thread) {
                if ($thread->getId() === $thread_id) {
                    $this->thread = $thread;
                    break;
                }
            }
        }
        if (!BlubberThread::findOneBySQL("context_type = :type AND context_id = :context_id AND visible_in_stream = '1' AND content IS NULL", ['context_id' => Context::get()->id, 'type' => Context::getType()])) {
            //create the default-thread for this context
            $coursethread = new BlubberThread();
            $coursethread['user_id'] = $GLOBALS['user']->id;
            $coursethread['external_contact'] = 0;
            $coursethread['context_type'] = Context::getType();
            $coursethread['context_id'] = Context::get()->id;
            $coursethread['visible_in_stream'] = 1;
            $coursethread['commentable'] = 1;
            $coursethread->store();
            $this->threads[] = $coursethread;
        }
        if (!$this->thread || Request::get("thread") === "new") {
            $this->thread = array_pop(array_reverse($this->threads));
        }
        $this->thread->markAsRead();

        $this->thread_data = $this->thread->getJSONData();

        if (!Avatar::getAvatar($GLOBALS['user']->id)->is_customized() && !$_SESSION['already_asked_for_avatar']) {
            $_SESSION['already_asked_for_avatar'] = true;
            PageLayout::postInfo(sprintf(_("Wollen Sie ein Avatar-Bild nutzen? %sLaden Sie jetzt ein Bild hoch%s."), '<a href="'.URLHelper::getURL("dispatch.php/avatar/update/user/".$GLOBALS['user']->id).'" data-dialog>', '</a>'));
        }
        $this->buildSidebar();

        $tf = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH']."/app/views");
        if (Request::isDialog()) {
            PageLayout::setTitle($this->thread->getName());
            $template = $tf->open("blubber/dialog");
        } else {
            $template = $tf->open("blubber/index");
            $template->set_layout($GLOBALS['template_factory']->open("layouts/base"));
        }

        $template->set_attributes($this->get_assigned_variables());
        $this->render_text($template->render());
    }

    protected function buildSidebar()
    {
        $sidebar = Sidebar::Get();
        $threads_widget = new BlubberThreadsWidget();

        foreach ($this->threads as $thread) {
            $threads_widget->addThread($thread);
        }

        if ($this->thread) {
            $threads_widget->setActive($this->thread->getId());
        }

        $sidebar->addWidget($threads_widget, "threads");
    }
}
