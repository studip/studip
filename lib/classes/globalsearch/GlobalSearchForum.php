<?php
/**
 * GlobalSearchModule for forum entries
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchForum extends GlobalSearchModule implements GlobalSearchFulltext
{
    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Forenbeiträge');
    }

    /**
     * Returns the filters that are displayed in the sidebar of the global search.
     *
     * @return array Filters for this class.
     */
    public static function getFilters()
    {
        return ['semester'];
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param string $search the input query string
     * @param array $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        $search = str_replace(" ", "% ", $search);
        $query = DBManager::get()->quote("%$search%");

        // visibility
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $seminaruser = " AND EXISTS (
                SELECT 1 FROM `seminar_user`
                WHERE `forum_entries`.`seminar_id` = `seminar_user`.`seminar_id`
                  AND `seminar_user`.`user_id` = " . DBManager::get()->quote($GLOBALS['user']->id) . "
              ) ";
        }

        // generate SQL condition for the semester filter in the sidebar
        if ($filter['category'] == self::class || $filter['category'] == "show_all_categories") {
            if ($filter['semester'] != "") {
                $semester = Semester::findByTimestamp($filter['semester']);
                $semester_condition = " AND (`mkdate` >= " . DBManager::get()->quote($semester['beginn']) .
                            " AND `mkdate` <= " . DBManager::get()->quote($semester['ende']) . ") ";
            }
        }

        // anonymous postings
        if (!$GLOBALS['perm']->have_perm('root') && Config::get()->FORUM_ANONYMOUS_POSTINGS) {
            $anonymous = "`anonymous` = 0 AND";
        } else {
            $anonymous = "";
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS `forum_entries`.*
                FROM `forum_entries`
                WHERE {$anonymous} (
                    `name` LIKE {$query}
                    OR `content` LIKE {$query}
                )
                {$semester_condition}
                {$seminaruser}
                ORDER BY `chdate` DESC
                LIMIT " . $limit;

        return $sql;
    }

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param array $data
     * @param string $search
     * @return array
     */
    public static function filter($data, $search)
    {
        $user = self::fromCache("user/{$data['user_id']}", function () use ($data) {
            return User::findFull($data['user_id']);
        });
        $course = self::fromCache("range/{$data['seminar_id']}", function () use ($data) {
            return Course::find($data['seminar_id']);
        });

        // Get name
        $name = _('Ohne Titel');
        if ($data['name']) {
            $name = self::mark($data['name'], $search);
        } elseif ($course) {
            $name = htmlReady($course->getFullname());
        }

        // Get additional info
        if ($user && !$data['anonymous']) {
            $temp = $user->getFullname();
        } else {
            $temp = _('Anonym');
        }
        $additional = sprintf(
            _('Beitrag von %1$s in %2$s'),
            $temp,
            $course ? $course->getFullname() : _('Ohne Titel')
        );

        // Clear content from blockquotes
        $filtered_content = self::blockquote_filter($data['content']);

        //in case the search query is found in either $name or $filtered_content (via direct_search), the result should be returned
        if (mb_strpos($name, "<mark>") !== false || self::direct_search($filtered_content, $search)) {
            $result = [
                'id'          => $data['topic_id'],
                'name'        => $name,
                'url'         => URLHelper::getURL(
                    "plugins.php/coreforum/index/index/{$data['topic_id']}#{$data['topic_id']}",
                    ['cid' => $data['seminar_id'], 'highlight_topic' => $data['topic_id']],
                    true
                ),
                'img'         => CourseAvatar::getAvatar($course->id)->getUrl(Avatar::MEDIUM),
                'date'        => strftime('%x', $data['chdate']),
                'description' => self::mark($filtered_content, $search, true),
                'additional'  => htmlReady($additional),
                'expand' => URLHelper::getURL('plugins.php/coreforum/index/search', [
                    'cid'            => $data['seminar_id'],
                    'backend'        => 'search',
                    'searchfor'      => $search,
                    'search_title'   => 1,
                    'search_content' => 1,
                    'search_author'  => 1
                ]),
                'expandtext'  => _('Im Forum dieser Veranstaltung suchen'),
                'user'        => $temp
            ];

            return $result;
        }
    }

    /**
     * Looks for blockquote and removes it from content in recursive mode
     *
     * @param string $content data content
     * @return string purified content (without blockquote)
     */
    public static function blockquote_filter($content)
    {
        $beg = '<blockquote>';
        $end = '</blockquote>';
        $beg_pos = mb_strpos($content, $beg);
        $end_pos = mb_strpos($content, $end);
        if ($beg_pos === false || $end_pos === false) {
            return $content;
        }

        $quote = mb_substr($content, $beg_pos, ($end_pos + mb_strlen($end)) - $beg_pos);
        $most_inner_element = mb_substr($quote, mb_strrpos($quote, $beg), (mb_strpos($quote, $end) + mb_strlen($end)) - mb_strrpos($quote, $beg));

        //recursive call!
        return self::blockquote_filter(str_replace($most_inner_element, '', $content));
    }

    /**
     * Search the query directly inside the string
     *
     * @param string $string text to be searched
     * @param string $query query to be found
     *
     * @return boolean $found in case of finding true, otherwise false
     */
    public static function direct_search($string, $query)
    {
        $found = false;
        $string = strip_tags($string);
        $query = trim($query);
        $quoted = preg_quote($query, '/');
        if (preg_match("/{$quoted}/Si", $string)) {
            $found = true;
        }
        return $found;
    }

    /**
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE `forum_entries` ADD FULLTEXT INDEX globalsearch (`name`, `content`)");
    }

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON `forum_entries`");
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * @param string $searchterm what to search for?
     * @return string URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }
}
