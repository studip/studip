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

        if (Navigation::hasItem('/course/wiki/show')) {
            Navigation::activateItem('/course/wiki/show');
        }
    }

    /**
     * Display dialog to create a new wiki page.
     */
    public function create_action()
    {
        getShowPageInfobox($this->keyword, true);
    }

    /**
     * change course permissions of wiki pages
     */
    public function change_courseperms_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Berechtigungen Wiki-Seiten zu ändern!'));
        }

        // prevent malformed urls: keyword must be set
        if (!$this->keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

        PageLayout::setTitle(_('Wiki-Einstellungen ändern'));

        $this->restricted = CourseConfig::get($this->range_id)->WIKI_COURSE_EDIT_RESTRICTED;

        getShowPageInfobox($this->keyword, true);
    }

    /**
     * store course permissions of wiki pages
     */
    public function store_courseperms_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Berechtigungen Wiki-Seiten zu ändern!'));
        }

        // prevent malformed urls: keyword must be set
        if (!$this->keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

        CourseConfig::get($this->range_id)->store(
            'WIKI_COURSE_EDIT_RESTRICTED',
            Request::int('courseperms')
        );
        PageLayout::postSuccess(_('Die veranstaltungsbezogenen Berechtigungen auf die Wiki-Seiten wurden geändert!'));
        $this->redirect(URLHelper::getURL('wiki.php', ['keyword' => $this->keyword]));
    }

    /**
    * change page permission of a single wiki page
    */
    public function change_pageperms_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Berechtigungen Wiki-Seiten zu ändern!'));
        }

        // prevent malformed urls: keyword must be set
        if (!$this->keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

        $page = WikiPage::findLatestPage($this->range_id, $this->keyword);

        PageLayout::setTitle(_('Seiten-Einstellungen ändern'));

        $this->config = $page->config;

        getShowPageInfobox($this->keyword, true);
    }

    /**
     * store page permissions of a wiki page
     */
    public function store_pageperms_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Berechtigungen von Wiki-Seiten zu ändern!'));
        }

        // prevent malformed urls: keyword must be set
        if (!$this->keyword) {
            throw new InvalidArgumentException(_('Es wurde keine Seite übergeben!'));
        }

        $wiki_page_config = new WikiPageConfig([$this->range_id, $this->keyword]);
        $wiki_page_config->read_restricted = Request::int('page_read_perms');
        $wiki_page_config->edit_restricted = Request::int('page_edit_perms');

        if (Request::int('page_global_perms') || $wiki_page_config->isDefault()) {
            WikiPageConfig::deleteBySQL('range_id = ? AND keyword = ?', [$this->range_id, $this->keyword]);
        } else {
            $wiki_page_config->store();
        }

        PageLayout::postSuccess(sprintf(
            _('Die Berechtigungen für Wiki-Seite "%s" wurden geändert!'),
            htmlReady($this->keyword)
        ));
        $this->redirect(URLHelper::getURL('wiki.php', ['keyword' => $this->keyword]));
    }

    public function store_action($version)
    {
        $body = Studip\Markup::purifyHtml(Request::get('body'));

        submitWikiPage($this->keyword, $version, $body, $GLOBALS['user']->id, $this->range_id);

        $latest_version = getLatestVersion($this->keyword, $this->range_id);

        if (Request::isXhr()) {
            $this->render_json([
                'version'  => $latest_version['version'],
                'body'     => $latest_version['body'],
                'messages' => implode(PageLayout::getMessages()) ?: false,
                'zusatz'   => getZusatz($latest_version),
            ]);
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
        $edit_perms = CourseConfig::get($course_id)->WIKI_COURSE_EDIT_RESTRICTED ? 'tutor' : 'autor';
        if (!$GLOBALS['perm']->have_studip_perm($edit_perms, $course_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Änderungen an Wikiseiten vorzunehmen!'));
        }

        $this->course = Course::find($course_id);
        if (!$this->course) {
            PageLayout::postError(
                _('Die ausgewählte Veranstaltung wurde nicht gefunden!')
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
                    'semtypes'  => [],
                    'exclude'   => [$course_id],
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
            $this->selected_course_id = Request::option('selected_course_id');
            $this->selected_course = Course::find($this->selected_course_id);

            if (!$this->selected_course) {
                $this->bad_course_search = true;
                return;
            }

            $this->wiki_pages = WikiPage::findLatestPages(
                $this->selected_course->id
            );
            $this->show_wiki_page_form = true;
        }

        //The import required additional functionality:
        if (Request::submitted('import')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_wiki_page_ids = Request::getArray('selected_wiki_page_ids');
            if (!$this->selected_wiki_page_ids) {
                PageLayout::postInfo(_('Es wurden keine Wikiseiten ausgewählt!'));
                return;
            }

            $selected_wiki_pages = [];
            foreach ($this->selected_wiki_page_ids as $id) {
                $wiki_page = WikiPage::find(json_decode($id, true));
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
                $latest_version = WikiPage::findLatestPage(
                    $this->course->id,
                    $selected_page->keyword
                );
                $new_page = new WikiPage();
                $new_page->range_id = $this->course->id;
                $new_page->user_id  = $selected_page->user_id;
                $new_page->keyword  = $selected_page->keyword;
                $new_page->body     = $selected_page->body;
                $new_page->chdate   = $selected_page->chdate;
                $new_page->version  = $latest_version ? $latest_version->version + 1 : 1;

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
