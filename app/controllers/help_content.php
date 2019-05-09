<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * help_content.php - Stud.IP-Help Content controller
 *
 * Copyright (C) 2014 - Arne Schröder <schroeder@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schröder <schroeder@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     help
*/

class HelpContentController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->help_admin = $GLOBALS['perm']->have_perm('root') || RolePersistence::isAssignedRole($GLOBALS['user']->id, 'Hilfe-Administrator(in)');

        $this->buildSidebar($action);
    }

    /**
     * Administration page for help content
     */
    public function admin_overview_action()
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }

        // initialize
        PageLayout::setTitle(_('Verwalten von Hilfe-Texten'));
        PageLayout::setHelpKeyword('Basis.HelpContentAdmin');
        // set navigation
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/help_content');
        } else {
            Navigation::activateItem('/tools/help_admin/help_content');
        }

        if (Request::get('help_content_filter') == 'set') {
            $this->help_content_searchterm = Request::option('help_content_filter_term');
        }
        if (Request::submitted('reset_filter')) {
            $this->help_content_searchterm = '';
        }
        if (Request::submitted('apply_help_content_filter')) {
            if (Request::get('help_content_searchterm') AND (mb_strlen(trim(Request::get('help_content_searchterm'))) < 3))
                PageLayout::postError(_('Der Suchbegriff muss mindestens 3 Zeichen lang sein.'));
            if (mb_strlen(trim(Request::get('help_content_searchterm'))) >= 3) {
                $this->help_content_searchterm = htmlReady(Request::get('help_content_searchterm'));
                $this->filter_text             = sprintf(_('Angezeigt werden Hilfe-Texte zum Suchbegriff "%s".'), $this->help_content_searchterm);
            }
        }

        // load help content
        $this->help_contents = HelpContent::GetContentByFilter($this->help_content_searchterm);
    }

    /**
     * Administration page for help content conflicts
     */
    public function admin_conflicts_action()
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }

        // initialize
        PageLayout::setTitle(_('Versions-Konflikte der Hilfe-Texte'));
        PageLayout::setHelpKeyword('Basis.HelpContentAdmin');
        // set navigation
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/help_content');
        } else {
            Navigation::activateItem('/tools/help_admin/help_content');
        }

        // load help content
        $this->conflicts = HelpContent::GetConflicts();
    }

    /**
     * resolves help content conflict
     *
     * @param String $id id of help content
     */
    public function resolve_conflict_action($id, $mode)
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }
        $GLOBALS['perm']->check('root');

        $this->help_content = HelpContent::GetContentByID($id);
        if ($mode == 'accept') {
            $this->help_content->studip_version = $GLOBALS['SOFTWARE_VERSION'];
            $this->help_content->store();
        } elseif ($mode == 'delete') {
            $this->help_content->delete();
        }

        $this->redirect('help_content/admin_conflicts');
    }

    /**
     * add new help content
     */
    public function add_action()
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }
        PageLayout::setTitle(_('Hilfe-Text erstellen'));

        $parameters = [];
        if (Request::get('from')) {
            $parameters['from'] = Request::get('from');
        }
        $this->parameters         = $parameters;
        $this->help_content_route = Request::get('help_content_route');

        $this->render_template('help_content/edit');
    }

    /**
     * edit help content
     *
     * @param String $id id of help content
     */
    public function edit_action($id)
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        PageLayout::setTitle(_('Hilfe-Text bearbeiten'));

        $parameters = [];
        if (Request::get('from')) {
            $parameters['from'] = Request::get('from');
        }
        $this->parameters = $parameters;

        $this->help_content       = HelpContent::GetContentByID($id);
        $this->help_content_route = $this->help_content->route;
        $this->help_content_id    = $id;
    }

    /**
     * Store changes
     *
     * @param string $id
     *
     * @throws InvalidSecurityTokenException
     */
    public function store_action($id = '')
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }
        CSRFProtection::verifySecurityToken();

        $content_id         = md5(uniqid('help_content', 1));
        $create_new_content = false;
        if ($id == '') {
            $create_new_content = true;
        } else {
            // load content by id
            $help_content = HelpContent::GetContentByID($id);
            // check if the language has been changed
            if (Request::option('help_content_language') && $help_content != Request::option('help_content_language') && Request::get('from')) {
                // check if a content exists for the given route and the language
                $help_content = HelpContent::findOneBySQL('`language` = ? AND `route` = ?', [Request::option('help_content_language'), Request::get('from')]);
                if (is_null($help_content)) {
                    $create_new_content = true;
                }
            }
        }

        // create new content
        if ($create_new_content) {
            $help_content = new HelpContent();
            $help_content->setNew(true);
        }

        if ($help_content->isNew()) {
            $help_content->global_content_id = $content_id;
            $help_content->content_id        = $content_id;
            $help_content->studip_version    = $GLOBALS['SOFTWARE_VERSION'];
            $help_content->position          = 1;
            $help_content->custom            = 1;
            $help_content->language          = Request::get('help_content_language') ?: mb_substr($GLOBALS['user']->preferred_language, 0, 2);
        }

        $help_content->content         = Request::get('help_content_content');
        $help_content->route           = Request::get('help_content_route');
        $help_content->author_email    = $GLOBALS['user']->Email;
        $help_content->installation_id = Config::get()->STUDIP_INSTALLATION_ID;

        if ($help_content->store()) {
            PageLayout::postSuccess(_('Der Hilfe-Text wurde erfolgreich gespeichert!'));
        }

        if (Request::isXhr() && Request::get('from')) {
            $this->response->add_header('X-Location', URLHelper::getURL(Request::get('from')));
            $this->render_nothing();
        } else {
            $this->relocate('help_content/admin_overview');
        }
    }

    /**
     * save settings
     */
    public function store_settings_action()
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->help_contents = HelpContent::GetContentByFilter(Request::get('help_content_searchterm'));
        $count               = 0;
        foreach ($this->help_contents as $help_content_id => $help_content_id) {
            // set status as chosen
            if ((Request::get('help_content_status_' . $help_content_id) == '1') && (!$this->help_contents[$help_content_id]->visible)) {
                $this->help_contents[$help_content_id]->visible = 1;
            } elseif ((Request::get('help_content_status_' . $help_content_id) != '1') && ($this->help_contents[$help_content_id]->visible)) {
                $this->help_contents[$help_content_id]->visible = 0;
            }
            if ($this->help_contents[$help_content_id]->store()) {
                $count++;
            }
        }

        if($count) {
            PageLayout::postSuccess(sprintf(ngettext('%u Änderung wurde durchgeführt', '%u Änderungen wurden durchgeführt', $count), $count));
        }
        $this->redirect('help_content/admin_overview');
    }

    /**
     * delete help content
     *
     * @param String $id id of help content
     */
    public function delete_action($id)
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        CSRFProtection::verifySecurityToken();
        PageLayout::setTitle(_('Hilfe-Text löschen'));

        $this->help_content = HelpContent::GetContentByID($id);
        if (is_object($this->help_content)) {
            if (Request::submitted('delete_help_content')) {
                PageLayout::postMessage(MessageBox::success(sprintf(_('Der Hilfe-Text zur Route "%s" wurde gelöscht.'), $this->help_content->route)));
                $this->help_content->delete();
                $this->response->add_header('X-Dialog-Close', 1);
                return $this->render_nothing();
            }
        }

        // prepare delete dialog
        $this->help_content_id = $id;
    }

    /**
     * Build local sidebar
     */
    private function buildSidebar($action)
    {
        $sidebar = Sidebar::get();
        $widget  = new ViewsWidget();

        $widget->addLink(
            _('Übersicht'),
            $this->url_for('help_content/admin_overview')
        )->setActive($action == 'admin_overview');

        $widget->addLink(
            _('Konflikte'),
            $this->url_for('help_content/admin_conflicts')
        )->setActive($action == 'admin_conflicts');

        $sidebar->addWidget($widget);

        if ($action == 'admin_overview') {
            $widget = new ActionsWidget();
            $widget->addLink(
                _('Hilfe-Text erstellen'),
                $this->url_for('help_content/add'),
                Icon::create('add', 'clickable'), ['data-dialog' => 'size=auto;reload-on-close', 'target' => '_blank']
            );
            $sidebar->addWidget($widget);
            $search = new SearchWidget('?apply_help_content_filter=1');
            $search->addNeedle(_('Suchbegriff'), 'help_content_searchterm');
            $sidebar->addWidget($search);
        }
    }
}
