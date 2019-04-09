<?php
namespace RESTAPI\Routes;

/**
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @condition course_id ^[a-f0-9]{32}$
 */
class Forum extends \RESTAPI\RouteMap
{

    public static function before()
    {
        require_once 'public/plugins_packages/core/Forum/models/ForumCat.php';
        require_once 'public/plugins_packages/core/Forum/models/ForumEntry.php';
        require_once 'public/plugins_packages/core/Forum/models/ForumPerm.php';
    }

    /**
     * List all categories of a forum
     *
     * @get /course/:course_id/forum_categories
     */
    public function getForumCategories($course_id)
    {
        if (!\ForumPerm::has('view', $course_id)) {
            $this->error(401);
        }

        $categories = \ForumCat::findBySeminar_id($course_id, 'ORDER BY pos ASC');
        $total      = sizeof($categories);
        $categories = array_splice($categories, (int)$this->offset, (int)$this->limit ?: 10);

        $json = [];
        foreach ($categories as $cat) {
            $json_cat = $cat->toArray();
            $uri = $this->urlf('/forum_category/%s', [htmlReady($json_cat['category_id'])]);
            $json_cat['course_id'] = $json_cat['seminar_id'];
            $json[$uri] = $this->categoryToJson($json_cat);
        }

        $this->etag(md5(serialize($json)));

        return $this->paginated($json, $total, compact('course_id'));
    }

    /**
     * Create a new category
     *
     * @post /course/:course_id/forum_categories
     */
    public function createForumCategory($course_id)
    {
        if (!\ForumPerm::has("add_category", $course_id)) {
            $this->error(401);
        }

        if (!isset($this->data['name']) || !mb_strlen($name = trim($this->data['name']))) {
            $this->error(400, 'Category name required.');
        }

        $category_id = \ForumCat::add($course_id, $name);
        if (!$category_id) {
            $this->error(500, 'Error creating the forum category.');
        }

        $this->redirect('forum_category/' . $category_id, 201, 'ok');
    }

    /**
     * Read a category
     *
     * @get /forum_category/:category_id
     */
    public function getForumCategory($category_id)
    {
        $category = $this->findCategory($category_id);
        $cid = $category['course_id'];

        if (!\ForumPerm::has('view', $cid)) {
            $this->error(401);
        }

        $category_json = $this->categoryToJson($category);
        $this->etag(md5(serialize($category_json)));
        return $category_json;
    }

    /**
     * Update a category
     *
     * @put /forum_category/:category_id
     */
    public function updateForumCategory($category_id)
    {
        $category = $this->findCategory($category_id);

        if (!\ForumPerm::has("edit_category", $category['course_id'])) {
            $this->error(401);
        }

        if (!isset($this->data['name']) || !mb_strlen($name = trim($this->data['name']))) {
            $this->error(400, 'Category name required.');
        }

        \ForumCat::setName($category_id, $this->data['name']);

        $this->status(204);
    }

    /**
     * Delete a category
     *
     * @delete /forum_category/:category_id
     */
    public function deleteForumCategory($category_id)
    {
        $category = $this->findCategory($category_id);
        $cid = $category['course_id'];

        if (!\ForumPerm::has("remove_category", $cid)) {
            $this->error(401);
        }

        \ForumCat::remove($category_id, $cid);

        $this->status(204);
    }

    /**
     * Show entries of a category
     *
     * @get /forum_category/:category_id/areas
     */
    public function getCategoryEntries($category_id)
    {
        $category = $this->findCategory($category_id);

        if (!\ForumPerm::has('view', $category['course_id'])) {
            $this->error(401);
        }

        $areas = $this->getAreas($category_id, $this->offset, $this->limit);

        $this->etag(md5(serialize($areas)));
        return $this->paginated($areas, $this->countAreas($category_id), compact('category_id'));
    }



    /**
     * Add a new forum entry to an existing one
     *
     * @post /forum_category/:category_id/areas
     */
    public function appendForumEntry($category_id)
    {
        $category = $this->findCategory($category_id);
        $cid = $category['course_id'];

        if (!\ForumPerm::has('add_area', $cid)) {
            $this->error(401);
        }

        if (!isset($this->data['subject']) || !mb_strlen($subject = trim($this->data['subject']))) {
            $this->error(400, 'Subject required.');
        }

        if (!isset($this->data['content'])) {
            $this->error(400, 'Content required.');
        }
        $content = trim($this->data['content']);

        $anonymous = isset($this->data['anonymous']) ? intval($this->data['anonymous']) : 0;

        $entry_id = $this->createEntry($cid, $cid, $subject, $content, $anonymous);

        \ForumCat::addArea($category_id, $entry_id);

        $this->redirect('forum_entry/' . $entry_id, 201, "ok");
    }

    /**
     * Get a forum entry
     *
     * @get /forum_entry/:entry_id
     */
    public function getForumEntry($entry_id)
    {
        $entry = \ForumEntry::getConstraints($entry_id);
        $cid   = $entry['seminar_id'];

        if (!\ForumPerm::has('view', $cid)) {
            $this->error(401);
        }
        
        $entry = $this->findEntry($entry_id);
        $this->lastmodified($entry->chdate);
        $this->etag(md5(serialize($entry)));
        return $entry;
    }

    /**
     * Add a new forum entry to an existing one
     *
     * @post /forum_entry/:entry_id
     */
    public function addForumEntry($parent_id)
    {
        $parent = $this->findEntry($parent_id);
        $cid = $parent['course_id'];

        $perm = self::isArea($entry) ? 'add_area' : 'add_entry';

        if (!\ForumPerm::has($perm, $cid)) {
            $this->error(401);
        }

        $subject = (string)trim($this->data['subject']);
        $content = (string)trim($this->data['content']);

        // areas and threads need a subject, postings do not
        if ($entry['depth'] < 3 && !$subject) {
            $this->error(400, 'Subject required.');
        }

        // all entries besides the area need content
        if ($entry['depth'] > 1 && !$content) {
            $this->error(400, 'Content required.');
        }

        if ($entry['depth'] >= 3 && $subject) {
            $this->error(400, 'Must not have subject here.');
        }

        $anonymous = isset($this->data['anonymous']) ? intval($this->data['anonymous']) : 0;

        $entry_id = $this->createEntry($parent_id, $cid, $subject, $content, $anonymous);

        $this->redirect('forum_entry/' . $entry_id, 201, "ok");
    }

    /**
     * Update an existing one forum entry
     *
     * @put /forum_entry/:entry_id
     */
    public function updateForumEntry($entry_id)
    {
        $entry = $this->findEntry($entry_id);
        $cid = $parent['course_id'];

        $perm = self::isArea($entry) ? 'edit_area' : 'edit_entry';

        if (!\ForumPerm::hasEditPerms($entry_id) || !\ForumPerm::has($perm, $cid)) {
            $this->error(401);
        }

        $subject = (string)trim($this->data['subject']);
        $content = (string)trim($this->data['content']);

        // areas and threads need a subject, postings do not
        if ($entry['depth'] < 3 && !$subject) {
            $this->error(400, 'Subject required.');
        }

        // all entries besides the area need content
        if ($entry['depth'] > 1 && !$content) {
            $this->error(400, 'Content required.');
        }

        if ($entry['depth'] >= 3 && $subject) {
            $this->error(400, 'Must not have subject here.');
        }

        \ForumEntry::update($entry_id, $subject, $content);

        $this->status(204);
    }

    /**
     * Delete an entry
     *
     * @delete /forum_entry/:entry_id
     */
    public function deleteForumEntry($entry_id)
    {
        $entry = $this->findEntry($entry_id);
        $cid = $parent['course_id'];

        if (!\ForumPerm::hasEditPerms($entry_id) || !\ForumPerm::has('remove_entry', $cid)) {
            $this->error(401);
        }

        \ForumEntry::delete($entry_id);

        $this->status(204);
    }

    /*********************
     *                   *
     * PRIVATE FUNCTIONS *
     *                   *
     *********************/


    private function findEntry($entry_id)
    {
        $raw = \ForumEntry::getConstraints($entry_id);
        if ($raw === false) {
            $this->notFound();
        }

        $entry = $this->convertEntry($raw);

        $children = \ForumEntry::getEntries($entry_id, \ForumEntry::WITHOUT_CHILDS, '', 'ASC', 0, false);

        if (isset($children['list'][$entry_id])) {
            unset($children['list'][$entry_id]);
        }

        $entry['children'] = [];
        foreach (array_values($children['list']) as $childentry) {
            $entry['children'][] = $this->convertEntry($childentry);
        }

        return $entry;
    }

    public function convertEntry($raw)
    {
        $entry = [];
        foreach(words("topic_id mkdate chdate anonymous depth") as $key) {
            $entry[$key] = $raw[$key];
        }

        $entry['subject']      = $raw['name'];
        $entry['user']         = $this->urlf('/user/%s', [$raw['user_id']]);
        $entry['course']       = $this->urlf('/course/%s', [$raw['seminar_id']]);
        $entry['content_html'] = \ForumEntry::getContentAsHtml($raw['content']);
        $entry['content']      = \ForumEntry::killEdit($raw['content']);

        return $entry;
    }


    private static function isArea($entry)
    {
        return 1 === $entry['depth'];
    }

    private function createEntry($parent_id, $course_id, $subject, $content, $anonymous)
    {
        $topic_id  = self::generateID();

        $data = [
            'topic_id'    => $topic_id,
            'seminar_id'  => $course_id,
            'user_id'     => $GLOBALS['user']->id,
            'name'        => $subject,
            'content'     => $content,
            'author'      => $GLOBALS['user']->getFullName(),
            'author_host' => $_SERVER['REMOTE_ADDR'],
            'anonymous'   => (int) $anonymous
        ];
        \ForumEntry::insert($data, $parent_id);

        return $topic_id;
    }

    private function findCategory($category_id)
    {
        $result = [];

        if ($cat = \ForumCat::get($category_id)) {
            $result = $cat;
            $result['course_id'] = $cat['seminar_id'];
            $result['name']      = $cat['entry_name'];
        } else {
            $this->error(404);
        }

        return $result;
    }

    private function categoryToJson($category)
    {
        $json = $category;

        $json['course'] = $this->urlf('/course/%s', [htmlReady($json['course_id'])]);
        unset($json['course_id']);

        $json['areas'] = $this->urlf('/forum_category/%s/areas', [$json['category_id']]);
        $json['areas_count'] = $this->countAreas($json['category_id']);

        return $json;
    }

    private function countAreas($category_id)
    {
        return sizeof(\ForumCat::getAreas($category_id));
    }

    private function getAreas($category_id, $offset = 0, $limit = 10)
    {
        $offset = (int) $offset;
        $limit  = (int) $limit;

        $areas = [];

        foreach (\ForumCat::getAreas($category_id, $offset, $limit) as $area) {
            $url = $this->urlf('/forum_entry/%s', [htmlReady($area['topic_id'])]);
            $areas[$url] = $this->convertEntry($area);
        }

        return $areas;
    }

    private static function generateID()
    {
        return md5(uniqid(rand()));
    }
}
