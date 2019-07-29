<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * tour.php - Stud.IP-Tour controller
 *
 * Copyright (C) 2013 - Arne Schröder <schroeder@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schröder <schroeder@data-quest.de>
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     help
*/

class TourController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->orientation_options = [
            'TL' => _('oben (links)'),
            'T'  => _('oben (mittig)'),
            'TR' => _('oben (rechts)'),
            'BL' => _('unten (links)'),
            'B'  => _('unten (mittig)'),
            'BR' => _('unten (rechts)'),
            'LT' => _('links (oben)'),
            'L'  => _('links (mittig)'),
            'LB' => _('links (unten)'),
            'RT' => _('rechts (oben)'),
            'R'  => _('rechts (mittig)'),
            'RB' => _('rechts (unten)'),
        ];

        $this->help_admin = $GLOBALS['perm']->have_perm('root')
                         || $GLOBALS['user']->getAuthenticatedUser()->hasRole('Hilfe-Administrator(in)');
    }

    /**
     * sends tour object as json data
     *
     * @param  string $tour_id id of tour object
     * @param  String $step_nr number of step to start with
     */
    public function get_data_action($tour_id, $step_nr = 1)
    {
        $this->route = get_route(Request::get('route'));
        $this->tour  = new HelpTour($tour_id);
        if (!$this->tour->isVisible() || !$this->route) {
            return $this->render_nothing();
        }

        $this->user_visit = new HelpTourUser([$tour_id, $GLOBALS['user']->user_id]);
        if ($this->user_visit->step_nr > 1 && !$_SESSION['active_tour']['step_nr'] && $this->tour->type === 'tour') {
            $data['last_run']      = sprintf(_('Wollen Sie die Tour "%s" an der letzten Position fortsetzen?'), $this->tour->name);
            $data['last_run_step'] = $this->user_visit->step_nr;
            $data['last_run_href'] = URLHelper::getURL($this->tour->steps[$this->user_visit->step_nr - 1]->route, null, true);
        } else {
            $_SESSION['active_tour'] = [
                'tour_id'        => $tour_id,
                'step_nr'        => $step_nr,
                'last_route'     => $this->tour->steps[$step_nr - 1]->route,
                'previous_route' => '',
                'next_route'     => '',
            ];
            $this->user_visit->step_nr = $step_nr;
            $this->user_visit->store();
        }
        $first_step = $step_nr;
        while ($first_step > 1 && $this->route === $this->tour->steps[$first_step - 2]->route) {
            $first_step--;
        }
        if ($first_step > 1 && $this->tour->type === 'tour') {
            $data['back_link'] = URLHelper::getURL($this->tour->steps[$first_step - 2]->route, null, true);
            $_SESSION['active_tour']['previous_route'] = $this->tour->steps[$first_step - 2]->route;
        }
        $data['route_step_nr'] = $first_step;
        $next_first_step       = $first_step;
        while ($this->route === $this->tour->steps[$next_first_step - 1]->route) {
            $data['data'][] = [
                'step_nr'     => $this->tour->steps[$next_first_step - 1]->step,
                'element'     => $this->tour->steps[$next_first_step - 1]->css_selector,
                'title'       => htmlReady($this->tour->steps[$next_first_step - 1]->title),
                'tip'         => formatReady($this->tour->steps[$next_first_step - 1]->tip),
                'route'       => $this->tour->steps[$next_first_step - 1]->route,
                'action_next' => $this->tour->steps[$next_first_step - 1]->action_next,
                'action_prev' => $this->tour->steps[$next_first_step - 1]->action_prev,
                'interactive' => ($this->tour->steps[$next_first_step - 1]->interactive ? '1' : ''),
                'orientation' => $this->tour->steps[$next_first_step - 1]->orientation];
            $next_first_step++;
        }
        if ($this->tour->steps[$step_nr - 1]->route !== $this->route) {
            $data['redirect'] = URLHelper::getURL($this->tour->steps[$step_nr - 1]->route, null, true);
        } elseif (!count($data['data']))
            return $this->render_nothing();
        if ($next_first_step <= count($this->tour->steps)) {
            if ($this->tour->type === 'tour') {
                $data['proceed_link'] = URLHelper::getURL($this->tour->steps[$next_first_step - 1]->route, null, true);
            }
            $_SESSION['active_tour']['next_route'] = $this->tour->steps[$next_first_step - 1]->route;
        }

        $data['edit_mode']         = $this->help_admin;
        $data['step_count']        = count($this->tour->steps);
        $data['controls_position'] = 'BR';
        $data['tour_type']         = $this->tour->type;
        $data['tour_title']        = htmlReady($this->tour->name);
        $template                  = $GLOBALS['template_factory']->open('tour/tour.php');
        $template->set_layout(null);
        $data['tour_html'] = $template->render();
        $this->set_content_type('application/json; charset=UTF-8');
        return $this->render_text(json_encode($data));
    }

    /**
     * sets session data for active tour
     *
     * @param String $tour_id tour id
     * @param String $step_nr number of current step
     * @param String $status  status of tour ('off' to end tour)
     */
    public function set_status_action($tour_id, $step_nr, $status)
    {
        // check permission
        $GLOBALS['perm']->check('user');
        $this->tour = new HelpTour($tour_id);
        if (!$this->tour->isVisible()) {
            return $this->render_nothing();
        }
        $this->user_visit = new HelpTourUser([$tour_id, $GLOBALS['user']->user_id]);
        $this->user_visit->step_nr = $step_nr;
        if ($status === 'off') {
            unset($_SESSION['active_tour']);
            if ($step_nr == count($this->tour->steps)) {
                $this->user_visit->completed = 1;
                $this->user_visit->step_nr   = 0;
            }
            $this->user_visit->store();
        } else {
            $_SESSION['active_tour'] = [
                'tour_id'    => $tour_id,
                'step_nr'    => $step_nr,
                'last_route' => $this->tour->steps[$step_nr - 1]->route,
            ];
            $this->user_visit->store();
            while ($this->tour->steps[$step_nr - 1]->route === $_SESSION['active_tour']['last_route'] && $step_nr < count($this->tour->steps)) {
                $step_nr += 1;
            }
            if ($this->tour->steps[$step_nr - 1]->route !== $_SESSION['active_tour']['last_route'])
                $_SESSION['active_tour']['next_route'] = $this->tour->steps[$step_nr - 1]->route;
        }
        $this->render_nothing();
    }

    /**
     * Administration page for tours
     *
     * @throws AccessDeniedException
     */
    public function admin_overview_action()
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }

        // initialize
        PageLayout::setTitle(_('Verwalten von Touren'));
        PageLayout::setHelpKeyword('Basis.TourAdmin');
        // set navigation
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/tour');
        } else {
            Navigation::activateItem('/tools/help_admin/tour');
        }

        if (Request::get('tour_filter') === 'set') {
            $this->tour_searchterm = Request::option('tour_filter_term');
        }
        if (Request::submitted('reset_filter')) {
            $this->tour_searchterm = '';
        }
        if (Request::submitted('apply_tour_filter')) {
            if (Request::get('tour_searchterm') && strlen(trim(Request::get('tour_searchterm'))) < 3) {
                PageLayout::postError(_('Der Suchbegriff muss mindestens 3 Zeichen lang sein.'));
            }
            if (strlen(trim(Request::get('tour_searchterm'))) >= 3) {
                $this->tour_searchterm = htmlReady(Request::get('tour_searchterm'));
                $this->filter_text     = sprintf(_('Angezeigt werden Touren zum Suchbegriff "%s".'), $this->tour_searchterm);
            }
        }
        // delete tour
        if (Request::option('confirm_delete_tour')) {
            CSRFProtection::verifySecurityToken();
            $this->delete_tour(Request::option('tour_id'));
        }
        // load tours
        $this->tours = HelpTour::GetToursByFilter($this->tour_searchterm);
        foreach ($this->tours as $tour_id => $tour) {
            if (Request::submitted('tour_remove_' . $tour_id)) {
                $this->delete_question = $this->delete_tour($tour_id);
            }
        }

        // save settings
        if (Request::submitted('save_tour_settings')) {
            foreach ($this->tours as $tour_id => $tour) {
                // set status as chosen
                if (Request::get('tour_status_' . $tour_id) && !$this->tours[$tour_id]->settings->active) {
                    $this->tours[$tour_id]->settings->active = 1;
                    $this->tours[$tour_id]->store();
                } elseif (!Request::get('tour_status_' . $tour_id) && $this->tours[$tour_id]->settings->active) {
                    $this->tours[$tour_id]->settings->active = 0;
                    $this->tours[$tour_id]->store();
                }
            }
        }
        $sidebar = Sidebar::get();

        $widget = new ViewsWidget();
        $widget->addLink(
            _('Übersicht'),
            $this->url_for('tour/admin_overview')
        )->setActive(true);
        $widget->addLink(
            _('Konflikte'),
            $this->url_for('tour/admin_conflicts')
        );
        $sidebar->addWidget($widget);

        $widget = new ActionsWidget();
        $widget->addLink(
            _('Tour erstellen'),
            $this->url_for('tour/admin_details'),
            Icon::create('add')
        );
        $widget->addLink(
            _('Tour importieren'),
            $this->url_for('tour/import'),
            Icon::create('add')
        )->asDialog('size=auto');
        $sidebar->addWidget($widget);

        $search = new SearchWidget('?apply_tour_filter=1');
        $search->addNeedle(_('Suchbegriff'), 'tour_searchterm', true);
        $sidebar->addWidget($search);
    }

    /**
     * import help tour
     */
    public function import_action()
    {
        // check permission
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        PageLayout::setTitle(_('Hilfe-Tour importieren'));

        if ($_FILES['tour_file']['tmp_name']) {
            $tour_json_data = file_get_contents($_FILES['tour_file']['tmp_name']);
            $tour_data = @json_decode($tour_json_data, true);
            if (!$tour_data || !$tour_data['tour']) {
                PageLayout::postError(_('Ungültige Daten. Tour-Daten müssen im JSON-Format vorliegen.'));
                return;
            }

            $this->metadata = $tour_data['metadata'];
            $this->tourdata = $tour_data['tour'];

            // import basic data
            $imported_tour = new HelpTour($tour_data['tour']['tour_id']);
            if (!$imported_tour->isNew()) {
                PageLayout::postError(sprintf(_('Es existiert bereits eine Tour mit dieser ID. Um sie zu ersetzen, müssen Sie die alte Tour "%s" erst löschen.'), $imported_tour->name));
            } else {
                $imported_tour->setData($tour_data['tour'], true);
                if ($imported_tour->store()) {
                    // import steps
                    foreach ($tour_data['tour']['steps'] as $step_data) {
                        $import_step = new HelpTourStep([$step_data['tour_id'], $step_data['step']]);
                        $import_step->setData($step_data, true);
                        $import_step->store();
                    }

                    // import audiences
                    if (is_array($tour_data['tour']['audiences'])) {
                        foreach ($tour_data['tour']['audiences'] as $audience_data) {
                            $import_audience = new HelpTourAudience([$audience_data['tour_id'], $audience_data['range_id'], $audience_data['type']]);
                            $import_audience->setData($audience_data, true);
                            $import_audience->store();
                        }
                    }

                    // import settings
                    $import_settings = new HelpTourSettings($tour_data['tour']['settings']['tour_id']);
                    $import_settings->setData($tour_data['tour']['settings'], true);
                    $import_settings->store();
                    PageLayout::postSuccess(_('Die Tour wurde importiert.'));
                } else {
                    PageLayout::postError(_('Keine Änderungen gespeichert.'));
                }
            }
        }
    }

    /**
     * export help tour
     *
     * @param String $id id of help tour
     */
    public function export_action($tour_id)
    {
        // check permission
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        // load tour
        $tour  = new HelpTour($tour_id);
        $tour_object = [];
        $tour_object['metadata'] = ['source' => $GLOBALS['UNI_INFO'], 'url' => $GLOBALS['UNI_URL'], 'version' => $GLOBALS['SOFTWARE_VERSION']];
        $tour_object['tour'] = $tour->toArrayRecursive();

        // set header
        $this->response->add_header(
            'Content-Disposition',
            'attachment;' . encode_header_parameter('filename', date('Y-m-d') . "-{$tour->name}.json")
        );
        $this->render_json($tour_object);
    }

    /**
     * delete tour
     *
     * @param String $tour_id tour id
     * @return string
     */
    private function delete_tour($tour_id)
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        $this->tour = new HelpTour($tour_id);
        if (Request::submitted('yes')) {
            CSRFProtection::verifySecurityToken();
            $this->response->add_header('X-Action', 'complete');
            $this->tour->delete();
        } elseif (Request::submitted('no')) {
            $this->response-add_header('X-Action', 'complete');
        } else {
            $this->response->add_header('X-Action', 'question');
            return createQuestion2(
                sprintf(_('Wollen Sie die Tour "%s" wirklich löschen?'), $this->tour->name),
                ['confirm_delete_tour' => 1, 'tour_id' => $tour_id],
                [],
                ''
            );
        }
        return '';
    }

    /**
     * removes tour step
     *
     * @param String $tour_id tour id
     * @param String $step_nr number of step
     * @return string
     */
    private function delete_step($tour_id, $step_nr)
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        if (Request::submitted('yes')) {
            CSRFProtection::verifySecurityToken();
            $this->response->add_header('X-Action', 'complete');
            $this->tour->deleteStep($step_nr);
        } elseif (Request::submitted('no')) {
            $this->response->add_header('X-Action', 'complete');
        } else {
            $this->response->add_header('X-Action', 'question');
            return createQuestion2(sprintf(
                _('Wollen Sie Schritt %s wirklich löschen?'), $step_nr),
                ['confirm_delete_tour_step' => 1, 'tour_id' => $tour_id, 'step_nr' => $step_nr],
                [],
                ''
            );
        }
        return '';
    }

    /**
     * delete tour step (ajax call)
     *
     * @param String $tour_id tour id
     * @param String $step_nr number of step
     */
    public function delete_step_action($tour_id, $step_nr)
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }
        $this->tour = new HelpTour($tour_id);
        $this->render_text($this->delete_step($tour_id, $step_nr));
    }

    /**
     * edit tour step
     *
     * @param String $tour_id tour id
     * @param String $step_nr number of step
     * @param String $mode    indicates edit mode (new, edit or save*)
     * @throws AccessDeniedException
     */
    public function edit_step_action($tour_id, $step_nr, $mode = 'edit')
    {
        if (!$this->help_admin) {
            return $this->render_nothing();
        }
        // Output as dialog (Ajax-Request) or as Stud.IP page?
        PageLayout::setTitle(_('Schritt bearbeiten'));
        // save step position
        if ($mode === 'save_position') {
            $temp_step = new HelpTourStep([$tour_id, $step_nr]);
            $temp_step->css_selector = trim(Request::get('position'));
            if ($temp_step->validate() && !$temp_step->isNew()) {
                $temp_step->store();
            }
            return $this->render_nothing();
        }
        // save step action (next)
        if ($mode === 'save_action_next') {
            $temp_step = new HelpTourStep([$tour_id, $step_nr]);
            $temp_step->action_next = trim(Request::get('position'));
            if ($temp_step->validate() && !$temp_step->isNew()) {
                $temp_step->store();
            }
            return $this->render_nothing();
        }
        // save step action (prev)
        if ($mode === 'save_action_prev') {
            $temp_step = new HelpTourStep([$tour_id, $step_nr]);
            $temp_step->action_prev = trim(Request::get('position'));
            if ($temp_step->validate() && !$temp_step->isNew()) {
                $temp_step->store();
            }
            return $this->render_nothing();
        }
        // save step
        if ($mode === 'save') {
            CSRFProtection::verifySecurityToken();
            if (Request::option('tour_step_editmode') == 'new') {
                $this->tour = new HelpTour($tour_id);
                if ($tour_id && $this->tour->isNew()) {
                    throw new AccessDeniedException(_('Die Tour mit der angegebenen ID existiert nicht.'));
                }
                $step_data = [
                    'title'        => trim(Request::get('step_title')),
                    'tip'          => trim(Request::get('step_tip')),
                    'interactive'  => trim(Request::get('step_interactive')),
                    'route'        => trim(Request::get('step_route')),
                    'css_selector' => trim(Request::get('step_css')),
                    'action_prev'  => trim(Request::get('action_prev')),
                    'action_next'  => trim(Request::get('action_next')),
                    'orientation'  => trim(Request::get('step_orientation')),
                    'mkdate'       => time(),
                    'author_email' => $GLOBALS['user']->Email,
                ];
                if ($this->tour->addStep($step_data, $step_nr)) {
                    $this->response->add_header('X-Dialog-Close', 1);
                } else {
                    $mode = 'new';
                }
            } else {
                $temp_step               = new HelpTourStep([$tour_id, $step_nr]);
                $temp_step->title        = trim(Request::get('step_title'));
                $temp_step->tip          = trim(Request::get('step_tip'));
                $temp_step->interactive  = trim(Request::get('step_interactive'));
                $temp_step->route        = trim(Request::get('step_route'));
                $temp_step->css_selector = trim(Request::get('step_css'));
                $temp_step->action_next  = trim(Request::get('action_next'));
                $temp_step->action_prev  = trim(Request::get('action_prev'));
                $temp_step->orientation  = Request::option('step_orientation');
                $temp_step->author_email = $GLOBALS['user']->Email;
                if ($temp_step->validate()) {
                    $temp_step->store();
                    $this->response->add_header('X-Dialog-Close', 1);
                } else {
                    $mode = 'edit';
                }
            }
        }

        // prepare edit dialog
        $this->tour_id = $tour_id;
        if ($mode === 'new') {
            $this->step       = new HelpTourStep();
            $this->step->step = $step_nr;
            $temp_step        = new HelpTourStep([$tour_id, $step_nr - 1]);
            if (!$temp_step->isNew()) {
                $this->step->route = $temp_step->route;
            }
        } else {
            $this->step = new HelpTourStep([$tour_id, $step_nr]);
        }
        if (Request::option('hide_route')) {
            $this->force_route = $this->step->route;
        }
        $this->mode = $mode;
    }

    /**
     * Administration page for tour conflicts
     * @throws AccessDeniedException
     */
    public function admin_conflicts_action()
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }

        // initialize
        PageLayout::setTitle(_('Versions-Konflikte der Touren'));
        PageLayout::setHelpKeyword('Basis.TourAdmin');
        // set navigation
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/tour');
        } else {
            Navigation::activateItem('/tools/help_admin/tour');
        }

        // load help content
        $this->conflicts = HelpTour::GetConflicts();

        $this->diff_fields = [
            'description'    => _('Beschreibung'),
            'studip_version' => _('Stud.IP-Version'),
            'type'           => _('Art der Tour'),
            'roles'          => _('Geltungsbereich'),
        ];
        $this->diff_step_fields = [
            'title'       => _('Titel'),
            'tip'         => _('Inhalt'),
            'interactive' => _('Interaktiv'),
            'route'       => _('Seite'),
            'orientation' => _('Orientierung'),
        ];
    }

    /**
     * resolves tour conflict
     *
     * @param String $id tour id
     * @throws AccessDeniedException
     */
    public function resolve_conflict_action($id, $mode)
    {
        // check permission
        if (!$this->help_admin) {
            return $this->render_nothing();
        }

        $this->tour = new HelpTour($id);
        if ($mode === 'accept') {
            $this->tour->studip_version = $GLOBALS['SOFTWARE_VERSION'];
            $this->tour->store();
        } elseif ($mode === 'delete') {
            $this->tour->delete();
        }
        $this->redirect('tour/admin_conflicts');
    }

    /**
     * Administration page for single tour
     *
     * @param String $tour_id tour id
     * @throws AccessDeniedException
     */
    public function admin_details_action($tour_id = '')
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }
        // initialize
        PageLayout::setTitle(_('Verwalten von Touren'));
        PageLayout::setHelpKeyword('Basis.TourAdmin');
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/tour');
        } else {
            Navigation::activateItem('/tools/help_admin/tour');
        }

        $this->tour = new HelpTour($tour_id);
        if ($tour_id && $this->tour->isNew()) {
            throw new AccessDeniedException(_('Die Tour mit der angegebenen ID existiert nicht.'));
        }

        foreach ($this->tour->steps as $step) {
            if (Request::option('confirm_delete_tour_step') == $step->step) {
                CSRFProtection::verifyUnsafeRequest();
                $this->delete_step($this->tour->id, $step->step);
            } elseif (Request::option('delete_tour_step') == $step->step) {
                $this->delete_question = $this->delete_step($this->tour->tour_id, $step->step);
                if (Request::submitted('yes') || Request::submitted('no')) {
                    $this->redirect('tour/admin_details/' . $this->tour->tour_id);
                }
            }
        }
        if (Request::option('tour_type')) {
            $this->tour->name             = trim(Request::get('tour_name'));
            $this->tour->description      = trim(Request::get('tour_description'));
            $this->tour->type             = Request::option('tour_type');
            $this->tour->settings->access = Request::option('tour_access');
            $this->tour->roles            = implode(',', Request::getArray('tour_roles'));
            $this->tour_startpage         = Request::get('tour_startpage');
        }
    }

    /**
     * save tour data
     *
     * @param String $tour_id tour id
     * @throws AccessDeniedException
     */
    public function save_action($tour_id = '')
    {
        // check permission
        if (!$this->help_admin) {
            throw new AccessDeniedException();
        }
        // initialize
        if ($GLOBALS['perm']->have_perm('root')) {
            Navigation::activateItem('/admin/config/tour');
        } else {
            Navigation::activateItem('/tools/help_admin/tour');
        }

        $this->tour = new HelpTour($tour_id);
        $this->tour->settings = new HelpTourSettings($tour_id);
        if ($tour_id AND $this->tour->isNew()) {
            throw new AccessDeniedException(_('Die Tour mit der angegebenen ID existiert nicht.'));
        }

        if (Request::submitted('save_tour_details')) {
            CSRFProtection::verifySecurityToken();
            $this->tour->name        = trim(Request::get('tour_name'));
            $this->tour->description = trim(Request::get('tour_description'));
            if (Request::option('tour_language')) {
                $this->tour->language = Request::option('tour_language');
            }
            $this->tour->type             = Request::option('tour_type');
            $this->tour->settings->access = Request::option('tour_access');
            $this->tour->roles            = implode(',', Request::getArray('tour_roles'));
            if ($this->tour->isNew()) {
                $this->tour->global_tour_id   = md5(uniqid('help_tours', 1));
                $this->tour->settings->active = 0;
            }
            $this->tour->author_email   = $GLOBALS['user']->Email;
            $this->tour->studip_version = $GLOBALS['SOFTWARE_VERSION'];
            if ($this->tour->validate()) {
                $this->tour->store();
                if (!count($this->tour->steps)) {
                    $step_data = [
                        'title'        => '',
                        'tip'          => _('(Neue Tour)'),
                        'interactive'  => 0,
                        'route'        => trim(Request::get('tour_startpage')),
                        'css_selector' => '',
                        'action_prev'  => '',
                        'action_next'  => '',
                        'orientation'  => '',
                        'mkdate'       => time(),
                        'author_email' => $GLOBALS['user']->Email,
                    ];
                    $this->tour->addStep($step_data, 1);
                    $this->tour_startpage = trim(Request::get('tour_startpage'));
                }
                PageLayout::postSuccess(_('Die Angaben wurden gespeichert.'));
            } else {
                $roles = '';
                if (count(Request::getArray('tour_roles'))) {
                    foreach (Request::getArray('tour_roles') as $role) {
                        $roles .= '&tour_roles[]=' . $role;
                    }
                }
                $this->redirect('tour/admin_details?tour_name=' . Request::get('tour_name')
                                . '&tour_language=' . Request::get('tour_language')
                                . '&tour_description=' . Request::get('tour_description')
                                . '&tour_type=' . Request::get('tour_type')
                                . '&tour_access=' . Request::get('tour_access')
                                . '&tour_startpage=' . Request::get('tour_startpage')
                                . $roles);
            }
        }
        $this->redirect('tour/admin_details/' . $this->tour->tour_id);
    }
}
