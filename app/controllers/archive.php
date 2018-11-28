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
        parent::before_filter($action, $args);
        
        $navigation = new Navigation(
            _('Archiv'),
            $this->url_for('archive')
        );
        
        $navigation->addSubNavigation(
            'overview',
            new Navigation(
                _('Übersicht'),
                $this->url_for('archive/overview/' . $args[0])
                )
        );
        
        $navigation->addSubNavigation(
            'forum',
            new Navigation(
                _('Forum'),
                $this->url_for('archive/forum/' . $args[0])
                )
        );
        
        $navigation->addSubNavigation(
            'wiki',
            new Navigation(
                _('Wiki'),
                $this->url_for('archive/wiki/' . $args[0])
                )
        );
        
        
        Navigation::addItem('/archive', $navigation);
        
    }
    
    
    private function buildSidebar()
    {
        $sidebar = Sidebar::get();
        
        $search = new SearchWidget(URLHelper::getUrl('dispatch.php/search/archive'));
        
        $search->addNeedle(
            _('Suche im Veranstaltungsarchiv'),
            'archivedCourse',
            _('Name der archivierten Veranstaltung')
        );
        
        $sidebar->addWidget($search);
    }
    
    /**
        To avoid code duplication this method is called from
        overview_action, forum_action and wiki_action, because
        those three actions just display different stuff
        from one ArchivedCourse object that is identified
        by the HTTP GET parameter courseId.
    */
    private function findArchivedCourse($courseId = null)
    {
        $this->course = false; //just in case we don't get a courseId
        if($courseId) {
            $this->course = ArchivedCourse::find($courseId);
        }
    }
    
    
    public function overview_action($courseId = null)
    {
        PageLayout::setTitle(_('Veranstaltungsarchiv'));
        $this->findArchivedCourse($courseId);
        Navigation::activateItem('archive/overview');
        $this->buildSidebar();
    }
    
    
    public function forum_action($courseId = null)
    {
        PageLayout::setTitle(_('Veranstaltungsarchiv'));
        $this->findArchivedCourse($courseId);
        Navigation::activateItem('archive/forum');
        $this->buildSidebar();
    }
    
    
    public function wiki_action($courseId = null)
    {
        PageLayout::setTitle(_('Veranstaltungsarchiv'));
        $this->findArchivedCourse($courseId);
        Navigation::activateItem('archive/wiki');
        $this->buildSidebar();
    }
    
    
    public function delete_action($course_id = null)
    {
        if (archiv_check_perm($course_id) != 'admin') {
            throw new AccessDeniedException();
        }

        $this->findArchivedCourse($course_id);
        if ($this->course) {
            $course_name = $this->course->name;
            if ($this->course->delete()) {
                PageLayout::postSuccess(
                    sprintf(
                        _('Die Veranstaltung %1$s wurde aus dem Archiv gelöscht!'),
                        $course_name
                    )
                );
            } else {
                PageLayout::postError(
                    sprintf(
                        _('Fehler beim Löschen der Veranstaltung %1$s aus dem Archiv!'),
                        $course_name
                    )
                );
            }
        }

        //This action is called from the course archive search page.
        //Because of that we should redirect to that page
        //when this action is finished:
        $this->redirect(
            URLHelper::getURL(
                'dispatch.php/search/archive',
                [
                    'criteria' => Request::get('criteria'),
                    'selectedSemester' => Request::get('selectedSemester'),
                    'selectedDepartment' => Request::get('selectedDepartment')
                ]
            )
        );
    }
}
