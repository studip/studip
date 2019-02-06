<?php
/**
 * The Search_ArchiveController enables users with sufficient permissions
 * to search the course archive.
 *
 * @author   Moritz Strohm <strohm@data-quest.de>
 * @license  GPL version 2 or any later version
 * @category Stud.IP
 * @since    3.5
 */


class Search_ArchiveController extends AuthenticatedController
{
    const SEARCH_LIMIT = 150;
    const NEEDLE_MIN_LENGTH = 3;

    /**
     * This action displays the main page of the archive search.
     * It is also responsible for handling search requests and showing
     * search results.
     *
     * If a search returned more than SEARCH_LIMIT results a message is
     * displayed informing the user that the search criteria must be more specific.
     */
    public function index_action()
    {
        if (!Config::get()->ENABLE_ARCHIVE_SEARCH) {
            throw new AccessDeniedException();
        }
        PageLayout::setHelpKeyword('Suche.Archiv');
        PageLayout::setTitle(_('Suche im Veranstaltungsarchiv'));

        if (Navigation::hasItem('/search/archive')) {
            Navigation::activateItem('/search/archive');
        }

        $this->criteria  = trim(Request::get('criteria'));
        $this->teacher   = trim(Request::get('teacher'));
        $this->semester  = Request::get('semester');
        $this->institute = Request::get('institute');

        // the optional parameter my_courses_only says that only the courses of
        // the current user shall be searched with the search criteria
        $this->my_courses_only = (bool) Request::int('my_courses_only');

        //build sidebar:
        Sidebar::get()->addWidget(new OptionsWidget())->addCheckbox(
            _('Nur eigene Veranstaltungen anzeigen'),
            $this->my_courses_only,
            URLHelper::getURL('dispatch.php/search/archive', [
                'criteria'        => $this->criteria,
                'teacher'         => $this->teacher,
                'semester'        => $this->semester,
                'institute'       => $this->institute,
                'my_courses_only' => 1,
            ]),
            URLHelper::getURL('dispatch.php/search/archive', [
                'criteria'  => $this->criteria,
                'teacher'   => $this->teacher,
                'semester'  => $this->semester,
                'institute' => $this->institute,
            ])
        );

        // get available semesters and institutes:
        $query = "SELECT semester
                  FROM archiv
                  WHERE semester != ''
                  GROUP BY semester
                  ORDER BY MAX(start_time) DESC";
        $this->semesters = DBManager::get()->fetchFirst($query);

        $query = "SELECT institute
                  FROM archiv
                  GROUP BY institute
                  ORDER BY institute ASC";
        $this->institutes = DBManager::get()->fetchFirst($query);

        // check if at least one search criteria was given:
        if (!$this->criteria && !$this->teacher && !$this->semester && !$this->institute) {
            return;
        }

        // search keyword is set and too short
        if ($this->criteria && mb_strlen($this->criteria) < self::NEEDLE_MIN_LENGTH) {
            PageLayout::postError(sprintf(
                _('Der Name der Veranstaltung muss mindestens %s Zeichen lang sein!'),
                self::NEEDLE_MIN_LENGTH
            ));
            return;
        }

        // teacher search keyword is set and too short
        if ($this->teacher && mb_strlen($this->teacher) < self::NEEDLE_MIN_LENGTH) {
            PageLayout::postError(sprintf(
                _('Der Name des/der gesuchten Lehrenden muss mindestens %s Zeichen lang sein!'),
                self::NEEDLE_MIN_LENGTH
            ));
            return;
        }

        $sql = '';
        $conditions = [];
        $parameters = [];

        // ok, checks are done: build SQL query:
        if ($this->my_courses_only) {
            $sql  = "INNER JOIN archiv_user ON archiv.seminar_id = archiv_user.seminar_id WHERE ";
            $conditions[] = "archiv_user.user_id = :user_id";
            $parameters[':user_id'] = User::findCurrent()->id;
        }

        if ($this->criteria) {
            $conditions[] = "(" . implode(' OR ', [
                "name LIKE CONCAT('%', :criteria, '%')",
                "untertitel LIKE CONCAT('%', :criteria, '%')",
                "beschreibung LIKE CONCAT('%', :criteria, '%')",
            ]) . ")";
            $parameters[':criteria'] = $this->criteria;
        }

        if ($this->teacher) {
            $conditions[] = "dozenten LIKE CONCAT('%', :teacher, '%')";
            $parameters[':teacher'] = $this->teacher;
        }

        if ($this->institute) {
            $conditions[] = "institute = :institute";
            $parameters[':institute'] = $this->institute;
        }

        if ($this->semester) {
            $conditions[] = "semester = :semester";
            $parameters[':semester'] = $this->semester;
        }

        $sql .= implode(' AND ', $conditions);
        $sql .= " ORDER BY start_time DESC, name DESC ";


        // first we count the courses: if there are too many
        // we won't collect them!
        $count = ArchivedCourse::countBySql($sql, $parameters);
        if ($count > self::SEARCH_LIMIT) {
            PageLayout::postError(sprintf(
                _('Es wurden %s Veranstaltungen gefunden. Bitte grenzen sie die Suchkriterien weiter ein!'),
                $count
            ));
        } elseif ($count === 0) {
            PageLayout::postInfo(_('Es wurde keine Veranstaltung gefunden!'));
        } else {
            $this->courses = ArchivedCourse::findBySQL($sql, $parameters);

            PageLayout::postInfo(sprintf(
                ngettext(
                    'Es wurde eine Veranstaltung gefunden!',
                    'Es wurden %s Veranstaltungen gefunden!',
                    count($this->courses)
                ),
                count($this->courses)
            ));
        }
    }
}
