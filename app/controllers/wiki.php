<?php
/**
 * wiki.php - wiki controller (currently only a helper)
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   3.3
 */
require_once 'lib/wiki.inc.php';

class WikiController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->keyword  = Request::get('keyword');
        $this->range_id = Context::getId();
    }

    /**
     * Display dialog to create a new wiki page.
     */
    public function create_action()
    {
        Navigation::activateItem('/course/wiki/show');
        $range_id = Context::getId();
        $this->storedStatusStandard = CourseConfig::get($range_id)->WIKI_COURSE_EDIT_PERM;

        $this->keyword  = Request::get('keyword');
        getShowPageInfobox($keyword, true);
    }

    /**
     * change course permissions of wiki pages
     */
    public function change_courseperms_action()
    {
        Navigation::activateItem('/course/wiki/show');
        $range_id = Context::getId();
        $this->storedStatus = CourseConfig::get($range_id)->WIKI_COURSE_EDIT_PERM;
        $this->keyword  = Request::get('keyword');
        $keyword = $this->keyword;
        $perm = $GLOBALS['perm'];

        getShowPageInfobox($keyword, true);

        if (!$perm->have_studip_perm("autor", $range_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Berechtigungen Wiki-Seiten zu ändern!'));
        }

        // prevent malformed urls: keyword must be set
        if (!$keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

    }

    public function change_pageperms_action()
    {
        Navigation::activateItem('/course/wiki/show');
        $this->keyword  = Request::get('keyword');
        $keyword = $this->keyword;
        $this->version = Request::int('version');
        $version = $this->version;

        $range_id = Context::getId();
        $row = WikiPageConfig::find([$range_id, $keyword]);
        $this->storedStatusEdit = $row->edit_perms;
        $this->storedStatusRead = $row->read_perms;

        $range_id = Context::getId();
        $this->storedStatusStandard = CourseConfig::get($range_id)->WIKI_COURSE_EDIT_PERM;

        if ($this->storedStatusRead == "") {
            $this->storedStatusRead = "autor";
        }

        getShowPageInfobox($keyword, true);

        // prevent malformed urls: keyword must be set
        if (!$keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

    }

    public function store_action($version)
    {
        $body = Studip\Markup::purifyHtml(Request::get('body'));

        submitWikiPage($this->keyword, $version, $body, $GLOBALS['user']->id, $this->range_id);

        $latest_version = getLatestVersion($this->keyword, $this->range_id);

        if (Request::isXhr()) {
            $this->render_json(array(
                'version'  => $latest_version['version'],
                'body'     => $latest_version['body'],
                'messages' => implode(PageLayout::getMessages()) ?: false,
                'zusatz'   => getZusatz($latest_version),
            ));
        } else {
            // Yeah, wait for the whole trailification of the wiki...
        }
    }

    public function version_check_action($version)
    {
        $latest_version = getLatestVersion($this->keyword, $this->range_id);

        if (!$latest_version && $version > 1) {
            $this->response->add_header('X-Studip-Error', _('Diese Wiki-Seite existiert nicht mehr!'));
            $this->render_json(false);
        } elseif ($latest_version && $version != $latest_version['version']) {
            $error  = _('Die von Ihnen bearbeitete Seite ist nicht mehr aktuell.') . ' ';
            $error .= _('Falls Sie dennoch speichern, überschreiben Sie die getätigte Änderung und es wird unter Umständen zu Datenverlusten kommen.');
            $this->response->add_header('X-Studip-Error', $error);

            $this->response->add_header('X-Studip-Confirm', _('Möchten Sie Ihre Version dennoch speichern?'));

            $this->render_json(null);
        } else {
            $this->render_json(true);
        }
    }

    /**
     * This action is responsible for importing wiki pages into the wiki
     * of a course from another course.
     */
    public function import_action($course_id = null)
    {
        $this->course = Course::find($course_id);
        if (!$this->course) {
            PageLayout::postError(
                _('Die gewählte Veranstaltung wurde nicht gefunden!')
            );
        }

        $all_semesters = Semester::getAll();
        $all_semester_ids = [];
        foreach ($all_semesters as $semester) {
            $all_semester_ids[] = $semester->id;
        }

        $this->course_search = new QuickSearch(
            'selected_course_id',
            new MyCoursesSearch(
                'Seminar_id',
                $GLOBALS['perm']->get_perm(),
                [
                    'userid'    => $GLOBALS['user']->id,
                    'semtypes'  => SemType::getGroupingSemTypes(),
                    'exclude'   => [Context::getId()],
                    'semesters' => $all_semester_ids,
                ],
                's.`Seminar_id` IN (
                    SELECT range_id FROM wiki
                    WHERE range_id = s.`Seminar_id`
                )'
            )
        );
        $this->course_search->fireJSFunctionOnSelect(
            "function() {jQuery(this).closest('form').submit();}"
        );

        //The following steps are identical for the search and the import.
        if (Request::submitted('selected_course_id') || Request::submitted('import')) {
            CSRFProtection::verifyUnsafeRequest();

            //Search for wiki pages in the selected course:
            $this->selected_course_id = Request::get('selected_course_id');
            $this->selected_course = Course::find($this->selected_course_id);

            if (!$this->selected_course) {
                PageLayout::postError(_('Die ausgewählte Veranstaltung wurde nicht gefunden!'));
            }

            $this->wiki_pages = WikiPage::findLatestPages(
                $this->selected_course->id
            );
            $this->show_wiki_page_form = true;
        }

        //The import required additional functionality:
        if (Request::submitted('import')) {
            $this->selected_wiki_page_ids = Request::getArray('selected_wiki_page_ids');
            if (!$this->selected_wiki_page_ids) {
                PageLayout::postInfo(_('Es wurden keine Wikiseiten ausgewählt!'));
                return;
            }

            $selected_wiki_pages = [];
            foreach ($this->selected_wiki_page_ids as $id) {
                $splitted_id = explode('_', $id);
                $wiki_page = WikiPage::findOneBySql('range_id = :range_id AND keyword = :keyword', [
                    'range_id' => $splitted_id[0],
                    'keyword'  => $splitted_id[1],
                ]);
                if ($wiki_page) {
                    $selected_wiki_pages[] = $wiki_page;
                }
            }

            if (!$selected_wiki_pages) {
                PageLayout::postError(_('Es wurden keine Wikiseiten gefunden!'));
                return;
            }

            $errors = [];
            foreach ($selected_wiki_pages as $selected_page) {
                //Check for an existing page first.
                $new_page = WikiPage::findOneBySql('range_id = :range_id AND keyword = :keyword', [
                    'range_id' => $this->course->id,
                    'keyword'  => $selected_page->keyword,
                ]);
                if (!$new_page) {
                    $new_page = new WikiPage();
                }
                $new_page->range_id = $this->course->id;
                $new_page->user_id  = $selected_page->user_id;
                $new_page->keyword  = $selected_page->keyword;
                $new_page->body     = $selected_page->body;
                $new_page->chdate   = $selected_page->chdate;
                $new_page->version += 1;

                if (!$new_page->store()) {
                    $errors[] = sprintf(
                        _('Fehler beim Import der Wikiseite %s!'),
                        $new_page->keyword
                    );
                }
            }
            if ($errors) {
                PageLayout::postError(
                    _('Die folgenden Fehler traten beim Import auf:'),
                    $errors
                );
            } else {
                $this->show_wiki_page_form = false;
                $this->success = true;
                PageLayout::postSuccess(
                    ngettext(
                        'Die Wikiseite wurde importiert! Sie ist unter dem Navigationspunkt "Alle Seiten" erreichbar.',
                        'Die Wikiseiten wurden importiert! Sie sind unter dem Navigationspunkt "Alle Seiten" erreichbar.',
                        count($selected_wiki_pages)
                    )
                );
            }
        }
    }
}
