<?php
namespace RESTAPI\Routes;

/**
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @condition course_id ^[0-9a-f]{32}$
 */
class Wiki extends \RESTAPI\RouteMap
{
    public function before()
    {
        require_once 'User.php';
        require_once 'lib/wiki.inc.php';
    }

    /**
     * Wikiseitenindex einer Veranstaltung
     *
     * @get /course/:course_id/wiki
     */
    public function getCourseWiki($course_id)
    {
        $pages = \WikiPage::findLatestPages($course_id);
        if (!sizeof($pages->findBy('keyword', 'WikiWikWeb'))) {
            $pages[] = \WikiPage::getStartPage($course_id);
        }

        if (!$pages->first()->isVisibleTo($GLOBALS['user']->id)) {
            $this->error(401);
        }

        $total = sizeof($pages);
        $pages = $pages->limit($this->offset, $this->limit);

        $linked_pages = [];
        foreach ($pages as $page) {
            $url = $this->urlf('/course/%s/wiki/%s', [$course_id, htmlReady($page['keyword'])]);
            $linked_pages[$url] = $this->wikiPageToJson($page, ["content"]);
        }

        $this->etag(md5(serialize($linked_pages)));

        return $this->paginated($linked_pages, $total, compact('course_id'));
    }

    /**
     * Wikiseite auslesen
     *
     * @get /course/:course_id/wiki/:keyword
     * @get /course/:course_id/wiki/:keyword/:version
     */
    public function getCourseWikiKeyword($course_id, $keyword, $version = null)
    {
        $page = $this->requirePage($course_id, $keyword, $version);
        $wiki_json = $this->wikiPageToJson($page);
        $this->etag(md5(serialize($wiki_json)));
        $this->lastmodified($page->chdate);
        return $wiki_json;
    }

    /**
     * Wikiseite ändern/hinzufügen
     *
     * @put /course/:course_id/wiki/:keyword
     */
    public function putCourseWikiKeyword($course_id, $keyword)
    {
        if (!isset($this->data['content'])) {
            $this->error(400, 'No content provided');
        }

        $last_version = \WikiPage::findLatestPage($course_id, $keyword);
        if (!$last_version) {
            $last_version = new \WikiPage([$course_id, $keyword, 0]);
        }

        if (!$last_version->isEditableBy($user_id = $GLOBALS['user']->id)) {
            $this->error(401);
        }

        // TODO: rewrite this code and put #submitWikiPage into
        // class \WikiPage
        if (!\Context::get()) {
            \Context::set($course_id);
        }
        submitWikiPage($keyword, $last_version->version, $this->data['content'], $user_id, $course_id);

        $new_version = \WikiPage::findLatestPage($course_id, $keyword);

        $url = sprintf('course/%s/wiki/%s/%d', htmlReady($course_id), htmlReady($keyword), $new_version->version);
        $this->redirect($url, 201, 'ok');
    }

    /**************************************************/
    /* PRIVATE HELPER METHODS                         */
    /**************************************************/

    private function requirePage($course_id, $keyword, $version)
    {
        if ($version) {
            $page = \WikiPage::find([$course_id, $keyword, $version]);
        } else {
            $page = \WikiPage::findLatestPage($course_id, $keyword);
        }

        if (!$page) {
            $this->notFound();
        }

        if (!$page->isVisibleTo($GLOBALS['user']->id)) {
            $this->error(401);
        }

        return $page;
    }

    private function wikiPageToJson($page, $without = [])
    {
        $json = $page->toArray(words("range_id keyword chdate version"));

        // (pre-rendered) content
        if (!in_array("content", $without)) {
            $json['content']      = $page->body;
            $json['content_html'] = wikiReady($page->body);
        }
        if (!in_array("user", $without)) {
            if($page->author) {
                $json['user'] = User::getMiniUser($this, $page->author);
            }
        }

        foreach ($without as $key) {
            if (isset($json[$key])) {
                unset($json[$key]);
            }
        }

        // string to int conversions as SORM does not know about ints
        foreach (words("chdate mkdate filesize downloads") as $key) {
            if (isset($result[$key])) {
                $result[$key] = intval($result[$key]);
            }
        }

        return $json;
    }


}
