<?php

class GlobalSearchBlubber extends GlobalSearchModule implements GlobalSearchFulltext
{
    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Blubber');
    }

    /**
     * Returns the filters that are displayed in the sidebar of the global search.
     *
     * @return array Filters for this class.
     */
    public static function getFilters()
    {
        return [];
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param string $search the input query string
     * @param arraay $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return string SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        $user_id = DBManager::get()->quote($GLOBALS['user']->id);

        $search = DBManager::get()->quote("%".$search."%");

        if (!$GLOBALS['perm']->have_perm("admin")) {
            return "SELECT SQL_CALC_FOUND_ROWS `blubber_threads`.`thread_id`, `blubber_comments`.`comment_id`
                FROM `blubber_threads`
                    LEFT JOIN `seminar_user` ON (`blubber_threads`.`context_id` = `seminar_id` AND `blubber_threads`.context_type = 'course')
                    LEFT JOIN `user_inst` ON (`blubber_threads`.`context_id` = `Institut_id` AND `blubber_threads`.context_type = 'institute')
                    LEFT JOIN `blubber_mentions` ON (`blubber_mentions`.`thread_id` = `blubber_threads`.`thread_id`)
                    LEFT JOIN `blubber_comments` ON (`blubber_comments`.`thread_id` = `blubber_threads`.`thread_id`)
                WHERE (
                        (`blubber_threads`.context_type = 'course' AND `seminar_user`.`user_id` = {$user_id})
                        OR (`blubber_threads`.context_type = 'institute' AND `user_inst`.`user_id` = {$user_id})
                        OR `blubber_threads`.context_type = 'public'
                        OR (`blubber_threads`.context_type = 'private' AND `blubber_mentions`.user_id = {$user_id})
                    )
                    AND (`blubber_threads`.content LIKE {$search} OR `blubber_comments`.content LIKE {$search})
                GROUP BY `blubber_threads`.`thread_id`
                ORDER BY `blubber_threads`.`mkdate` DESC
                LIMIT " . $limit;
        } elseif (!$GLOBALS['perm']->have_perm("root")) {
            return "SELECT SQL_CALC_FOUND_ROWS DISTINCT `blubber_threads`.`thread_id`, `blubber_comments`.`comment_id`
                FROM `blubber_threads`
                    LEFT JOIN `user_inst` ON (`blubber_threads`.`context_id` = `Institut_id` AND `blubber_threads`.context_type = 'institute')
                    LEFT JOIN `seminar_inst` ON (`seminar_inst`.institut_id = `user_inst`.Institut_id)
                    LEFT JOIN `blubber_mentions` ON (`blubber_mentions`.`thread_id` = `blubber_threads`.`thread_id`)
                    LEFT JOIN `blubber_comments` ON (`blubber_comments`.`thread_id` = `blubber_threads`.`thread_id`)
                WHERE (
                        (`blubber_threads`.context_type = 'institute' AND `user_inst`.`user_id` = {$user_id})
                        OR (`blubber_threads`.context_type = 'course' AND `user_inst`.`user_id` = {$user_id})
                        OR context_type = 'public'
                        OR (`blubber_threads`.context_type = 'private' AND `blubber_mentions`.user_id = {$user_id})
                    )
                    AND (`blubber_threads`.content LIKE {$search} OR `blubber_comments`.content LIKE {$search})
                GROUP BY `blubber_threads`.`thread_id`
                ORDER BY `blubber_threads`.`mkdate` DESC
                LIMIT " . $limit;
        } else { //I Am Root!
            return "SELECT SQL_CALC_FOUND_ROWS DISTINCT `blubber_threads`.`thread_id`, `blubber_comments`.`comment_id`
                FROM `blubber_threads`
                    LEFT JOIN `blubber_mentions` ON (`blubber_mentions`.`thread_id` = `blubber_threads`.`thread_id`)
                    LEFT JOIN `blubber_comments` ON (`blubber_comments`.`thread_id` = `blubber_threads`.`thread_id`)
                WHERE (
                        `blubber_threads`.context_type != 'private'
                        OR `blubber_mentions`.user_id = {$user_id}
                    )
                    AND (`blubber_threads`.content LIKE {$search} OR `blubber_comments`.content LIKE {$search})
                GROUP BY `blubber_threads`.`thread_id`
                ORDER BY `blubber_threads`.`mkdate` DESC
                LIMIT " . $limit;
        }
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
        $thread = BlubberThread::find($data['thread_id']);
        if (!$thread->isReadable()) {
            return null;
        }
        $description = null;
        if ($data['comment_id']) {
            $comment = BlubberComment::find($data['comment_id']);
            if ($comment && mb_stripos($comment['content'], $search) !== false) {
                $description = self::mark($comment['content'], $search, true);
                $name = sprintf(
                    $thread['content'] ? _("Kommentar von %s") : _("Nachricht von %s"),
                    $comment->user ? $comment->user->getFullName() : _("unbekannt")
                );
            }
        }
        if (!$description) {
            $description = self::mark($thread['content'], $search, true);
            $name = $thread->getName();
        }

        return [
            'id'          => $thread->getId(),
            'name'        => htmlReady($name),
            'url'         => $thread->getURL(),
            'img'         => $thread->getAvatar(),
            'date'        => strftime('%x', $thread['mkdate']),
            'description' => htmlReady($description),
            'additional'  => htmlReady($thread->getName()),
            'expand'      => $thread->getURL()
        ];
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

    /**
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE blubber_threads ADD FULLTEXT INDEX globalsearch (content)");
        DBManager::get()->exec("ALTER TABLE blubber_comments ADD FULLTEXT INDEX globalsearch (content)");
    }

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON blubber_threads");
        DBManager::get()->exec("DROP INDEX globalsearch ON blubber_comments");
    }
}
