<?php
/**
 * The Search_GlobalsearchController enables users with sufficient permissions
 * to search through the specified categories.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Manuel Schwarz <manschwa@uos.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    4.3
 */
class Search_GlobalsearchController extends AuthenticatedController
{
    /**
     * This action displays the main page of the global search.
     * It is also responsible for handling search requests and showing
     * search results.
     *
     * @return null This method does not return any value.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Globale Suche'));
        if (Navigation::hasItem('/search/globalsearch')) {
            Navigation::activateItem('/search/globalsearch');
        }

        PageLayout::addHeadElement('meta', [
            'name'    => 'studip-cache-prefix',
            'content' => md5("{$_COOKIE[Seminar_Session::class]}-{$GLOBALS['user']->id}"),
        ]);

        PageLayout::setBodyElementId('globalsearch-page');

        $this->addInfoText();
        $this->addSidebar();
    }

    /**
     * Add the sidebar to the search page including the searchable categories
     * and their filters.
     */
    private function addSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/search-sidebar.png');

        $links_widget = $sidebar->addWidget(new ViewsWidget());
        $links_widget->setTitle(_('Ergebnis-Anzeige'));
        $links_widget->id = 'category_widget';
        $links_widget->addLink(
            _('Alle Ergebnisse'),
            '#',
            null,
            ['id' => 'show_all_categories']
        )->setActive();

        $modules = GlobalSearchModule::getActiveSearchModules();
        $this->filters['show_all_categories'] = ['semester'];
        foreach ($modules as $class_name) {
            if (is_a($class_name, 'GlobalSearchModule', true)) {
                $this->filters[$class_name] = $class_name::getFilters();
                $links_widget->addLink(
                    $class_name::getName(),
                    '#' . $class_name,
                    null,
                    ['id' => "search_category_{$class_name}"]
                );
            }
        }

        $semester_filter = $sidebar->addWidget(new OptionsWidget(_('Semester')));
        $semester_filter->id = 'semester_filter';
        $semester_filter->addSelect(
            _('Semester'),
            null,
            'semester',
            $this->getSemesters(),
            (int) $_SESSION['global_search']['selects']['semester'],
            ['id' => 'semester_select']
        );

        $seminar_type_filter = $sidebar->addWidget(new OptionsWidget(_('Veranstaltungstypen')));
        $seminar_type_filter->id = 'seminar_type_filter';
        $seminar_type_filter->addSelect(
            _('Typ der Veranstaltung'),
            null,
            'seminar_type',
            $this->getSemClasses(),
            $_SESSION['global_search']['selects']['seminar_type'],
            ['id' => 'seminar_type_select']
        );

        $institute_filter = $sidebar->addWidget(new OptionsWidget(_('Einrichtungen')));
        $institute_filter->id = 'institute_filter';
        $institute_filter->addSelect(
            _('Einrichtung'),
            null,
            'institute',
            $this->getInstitutes(),
            $_SESSION['global_search']['selects']['institute'],
            ['id' => 'institute_select']
        );
    }

    /**
     * Get semesters for the semester-select-filter (dropdown).
     * The semester filter shows all available semesters
     * and sets the current semester as the selected default.
     *
     * @return array with key => value pairs like: array('semester_beginn' => 'semester_name')
     */
    private function getSemesters()
    {
        // set the current semester as the initially selected semester
        if (!$_SESSION['global_search']['selects']) {
            $current_sem = GlobalSearchModule::getCurrentSemester();
            $_SESSION['global_search']['selects']['semester'] = $current_sem;
        }
        $semesters = [];
        $semesters[''] = _('Alle Semester');

        $sems = array_reverse(Semester::getAll());
        foreach ($sems as $semester) {
            $semesters[$semester['beginn']] = $semester['name'];
        }
        return $semesters;
    }

    /**
     * Get institutes for the institute-select-filter (dropdown).
     * The institute filter shows all available institutes and presents the 2-level hierarchy with indented names.
     *
     * @return array with key => value pairs like: array('institute_id' => 'institute_name')
     */
    private function getInstitutes()
    {
        $institutes = [];
        $institutes[''] = _('Alle Einrichtungen');

        $insts = Institute::getInstitutes();
        foreach ($insts as $institute) {
            $institutes[$institute['Institut_id']] = ($institute['is_fak'] ? '' : '  ') . $institute['Name'];
        }
        return $institutes;
    }

    /**
     * Get seminar types for the seminar-type-select-filter (dropdown).
     * The seminar type filter shows all available seminar types and
     * seminar type classes which are presented as a 2-level hierarchy with indented names.
     *
     * @return array with key => value pairs like: array('seminar_type_id' => 'seminar_type_name')
     */
    private function getSemClasses()
    {
        $sem_classes = [];
        $sem_classes[''] = _('Alle Veranstaltungsarten');

        foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) {
            $sem_classes[$class_id] = $class['name'];
            if (!$class['studygroup_mode']) {
                foreach ($class->getSemTypes() as $type_id => $type) {
                    $sem_classes["{$class_id}_{$type_id}"] = "  {$type['name']}";
                }
            }
        }
        return $sem_classes;
    }

    /**
     * Add some information on how to use the search.
     */
    private function addInfoText()
    {
        Helpbar::get()->addPlainText(_('Platzhalter'), _('_ ist Platzhalter für ein beliebiges Zeichen. % ist Platzhalter für beliebig viele Zeichen. Me_er findet Treffer für Meyer und Meier. M__er findet zusätzlich auch Mayer und Maier. M%er findet alle vorherigen Treffer aber auch Münchner.'));
        Helpbar::get()->addPlainText(_('Klick auf Überschrift'), _('Erweitert die ausgewählte Suchkategorie, um mehr Suchergebnisse aus dieser Kategorie anzuzeigen. Ein weiterer Klick zeigt wieder Ergebnisse aus allen Kategorien an.'));
        Helpbar::get()->addPlainText(_('Dateisuche'), _('Die Dateisuche kann über einen Schrägstrich (/) verfeinert werden. Beispiel: "Meine Veranstaltung/Datei" zeigt alle Dateien, die das Wort "Datei" enthalten und in "Meine Veranstaltung" sind, an. Die Veranstaltung kann auch auf einen Teil (z.B. Veran/Datei) oder auf die Großbuchstaben bzw. auch deren Abkürzung (z.B. MV/Datei oder V/Datei) beschränkt werden.'));
    }
}
