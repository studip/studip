<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Moritz Strohm <strohm@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.5.alpha-svn
 */

class ArchiveController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        $sections = [
            'overview' => _('Übersicht'),
            'forum'    => _('Forum'),
            'wiki'     => _('Wiki'),
        ];

        parent::before_filter($action, $args);

        $navigation = new Navigation(_('Archiv'), $this->url_for('archive'));
        foreach ($sections as $key => $label) {
            $navigation->addSubNavigation($key, new Navigation(
                $label,
                $this->url_for("archive/{$key}/{$args[0]}")
            ));
        }
        Navigation::addItem('/archive', $navigation);

        // Set page title, activate appropriate navigation item and load course
        PageLayout::setTitle(_('Veranstaltungsarchiv'));

        if (Navigation::hasItem("/archive/{$action}")) {
            Navigation::activateItem("/archive/{$action}");
        }

        // Setup sidebar
        $search = new SearchWidget(URLHelper::getURL('dispatch.php/search/archive'));
        $search->addNeedle(
            _('Suche im Veranstaltungsarchiv'),
            'criteria',
            _('Name der archivierten Veranstaltung')
        );

        Sidebar::get()->addWidget($search);

    }

    public function overview_action($course_id)
    {
        $this->course = ArchivedCourse::find($course_id);
    }

    public function forum_action($course_id)
    {
        $this->course = ArchivedCourse::find($course_id);
    }

    public function wiki_action($course_id)
    {
        $this->course = ArchivedCourse::find($course_id);
    }

    public function delete_action($course_id)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        if (archiv_check_perm($course_id) !== 'admin') {
            throw new AccessDeniedException();
        }

        $course = ArchivedCourse::find($course_id);
        if ($course) {
            $course_name = $course->name;
            if ($course->delete()) {
                PageLayout::postSuccess(sprintf(
                    _('Die Veranstaltung %1$s wurde aus dem Archiv gelöscht!'),
                    htmlReady($course_name)
                ));
            } else {
                PageLayout::postError(sprintf(
                    _('Fehler beim Löschen der Veranstaltung %1$s aus dem Archiv!'),
                    htmlReady($course_name)
                ));
            }
        }

        // This action is called from the course archive search page.
        // Because of that we should redirect to that page when this action is
        // finished:
        $this->redirect(URLHelper::getURL('dispatch.php/search/archive', [
            'criteria'        => Request::get('criteria'),
            'teacher'         => Request::get('teacher'),
            'semester'        => Request::get('semester'),
            'institute'       => Request::get('institute'),
            'my_courses_only' => Request::int('my_courses_only'),
        ]));
    }
}
