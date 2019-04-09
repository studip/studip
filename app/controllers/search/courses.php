<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Rasmus Fuhse <fuhse@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */

class Search_CoursesController extends AuthenticatedController
{
    
    /**
     * @var string Holds the URL parameter with selected navigation option
     */
    private $nav_option = null;
    
    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;
        
        parent::before_filter($action, $args);
        
        PageLayout::setHelpKeyword('Basis.VeranstaltungenAbonnieren');
        PageLayout::setTitle(_('Suche nach Veranstaltungen'));

        // activate navigation item
        $nav_options = Config::get()->COURSE_SEARCH_NAVIGATION_OPTIONS;
        URLHelper::bindLinkParam('option', $this->nav_option);
        if ($nav_options[$this->nav_option]
                && Navigation::hasItem('/search/courses/' . $this->nav_option)) {
            Navigation::activateItem('/search/courses/' . $this->nav_option);
        } else {
            URLHelper::removeLinkParam('option');
            $level = Request::get('level', $_SESSION['sem_browse_data']['level']);
            $default_option = SemBrowse::getSearchOptionNavigation('sidebar');
            if (!$level) {
                $this->relocate($default_option->getURL());
            } elseif ($level == 'f' && $nav_options['courses']['visible']) {
                    Navigation::activateItem('/search/courses/courses');
            } elseif ($level == 'vv' && $nav_options['semtree']['visible']) {
                Navigation::activateItem('/search/courses/semtree');
            } elseif ($level == 'ev' && $nav_options['rangetree']['visible']) {
                Navigation::activateItem('/search/courses/rangetree');
            } else {
                throw new AccessDeniedException();
            }
        }
    }

    public function index_action()
    {
        SemBrowse::transferSessionData();
        $this->sem_browse_obj = new SemBrowse();
        
        if (!$GLOBALS['perm']->have_perm('root')) {
            $this->sem_browse_obj->target_url = 'dispatch.php/course/details/';
            $this->sem_browse_obj->target_id = 'sem_id';
        } else {
            $this->sem_browse_obj->target_url = 'seminar_main.php';
            $this->sem_browse_obj->target_id = 'auswahl';
        }
        
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/seminar-sidebar.png');

        SemBrowse::setSemesterSelector($this->url_for('search/courses/index'));
        SemBrowse::setClassesSelector($this->url_for('search/courses/index'));
        
        // add search options to sidebar
        if (Request::get('level', $_SESSION['sem_browse_data']['level'] ?: 'f') == 'f') {
            $widget = new OptionsWidget();
            $widget->setTitle(_('Suchoptionen'));
            $widget->addCheckbox(_('Erweiterte Suche anzeigen'),
                    $_SESSION['sem_browse_data']['cmd'] == "xts",
                    URLHelper::getLink('?cmd=xts&level=f'),
                    URLHelper::getLink('?cmd=qs&level=f'));
            $sidebar->addWidget($widget);
        }
        
        if ($this->sem_browse_obj->show_result
                && count($_SESSION['sem_browse_data']['search_result'])) {
            $actions = new ActionsWidget();
            $actions->addLink(_('Download des Ergebnisses'),
                    URLHelper::getURL('dispatch.php/search/courses/export_results'),
                    Icon::create('file-office', 'clickable'));
            $sidebar->addWidget($actions);

            $grouping = new OptionsWidget();
            $grouping->setTitle(_('Suchergebnis gruppieren:'));
            foreach ($this->sem_browse_obj->group_by_fields as $i => $field) {
                $grouping->addRadioButton(
                    $field['name'],
                    URLHelper::getLink('?', ['group_by' => $i,
                        'keep_result_set' => 1]),
                    $_SESSION['sem_browse_data']['group_by'] == $i
                );
            }
            $sidebar->addWidget($grouping);
        }
        
        // show information about course class if class was changed
        $class = $GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']];
        if (is_object($class) && $class->countSeminars() > 0) {
            if (trim($GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']]['description'])) {
                PageLayout::postInfo(sprintf(_('Gewählte Veranstaltungsklasse <i>%1s</i>: %2s'),
                        $GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']]['name'],
                        $GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']]['description']));
            } else {
                PageLayout::postInfo(sprintf(_('Gewählte Veranstaltungsklasse <i>%1s</i>.'),
                        $GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']]['name']));
            }
        } elseif ($_SESSION['sem_browse_data']['show_class'] != 'all') {
            PageLayout::postInfo(_('Im gewählten Semester ist in dieser Veranstaltungsklasse keine Veranstaltung verfügbar. Bitte wählen Sie eine andere Veranstaltungsklasse oder ein anderes Semester!'));
        }
        
        $this->controller = $this;
    }

    public function export_results_action()
    {
        $sem_browse_obj = new SemBrowse();
        $tmpfile = basename($sem_browse_obj->create_result_xls());
        if ($tmpfile) {
            $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                    $tmpfile, _('ErgebnisVeranstaltungssuche.xls'), 4));
        } else {
            $this->render_nothing();
        }
    }
    
}
