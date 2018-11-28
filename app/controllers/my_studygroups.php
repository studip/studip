<?php
class MyStudygroupsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/browse/my_studygroups/index');
        }
    }

    public function index_action()
    {
        PageLayout::setHelpKeyword('Basis.MeineStudiengruppen');
        PageLayout::setTitle(_('Meine Studiengruppen'));
        URLHelper::removeLinkParam('cid');

        $this->studygroups  = MyRealmModel::getStudygroups();
        $this->nav_elements = MyRealmModel::calc_single_navigation($this->studygroups);
        $this->set_sidebar();
    }

    public function set_sidebar()
    {
        if ($GLOBALS['user']->perms === 'user') {
            return;
        }

        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/studygroup-sidebar.png');
        $sidebar->setTitle(_('Meine Studiengruppen'));

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Studiengruppe anlegen'),
            URLHelper::getLink('dispatch.php/course/wizard', ['studygroup' => 1]),
            Icon::create('add')
        )->asDialog('size=auto');
        if (count($this->studygroups) > 0) {
            $actions->addLink(
                _('Farbgruppierung ändern'),
                URLHelper::getLink('dispatch.php/my_courses/groups/all/true'),
                Icon::create('group4')
            )->asDialog();
        }
        $sidebar->addWidget($actions);
    }
}
