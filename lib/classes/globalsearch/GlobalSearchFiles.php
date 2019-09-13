<?php
/**
 * Global search module for files
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchFiles extends GlobalSearchModule implements GlobalSearchFulltext
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Dateien');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * File search isn't that trivial, as not everything that is found can
     * also be seen/downloaded by the current user. So we fetch thrice the
     * number of entries we need, hoping something downloadable will remain.
     *
     * @param $search the input query string
     * @param $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        $query = DBManager::get()->quote('%' . trim($search) . '%');

        // Check if a path to a course was given.
        if (mb_strpos($search, '/') !== false) {

            $args = explode('/', $search);
            $prequery = DBManager::get()->quote("%" . trim($args[0]) . "%");
            $query = DBManager::get()->quote("%" . trim($args[1]) . "%");
            $binary = DBManager::get()->quote('%' . implode('%', preg_split(
                '//u',
                mb_strtoupper(trim($args[0])),
                null,
                PREG_SPLIT_NO_EMPTY
            )) . '%');
            $comp = "AND";

            switch ($GLOBALS['perm']->get_perm()) {
                // Roots see all files, no matter where.
                case 'root':
                    $mycourses = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `Seminar_id`
                                  FROM `seminare`
                                  WHERE (`Name` LIKE {$prequery}
                                    OR `VeranstaltungsNummer` LIKE {$prequery})
                                    {$semester_condition}";
                    break;

                /*
                 * Admins see courses at their own institutes.
                 */
                case 'admin':
                    $institutes = array_map(function ($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());

                    $mycourses = "SELECT SQL_CALC_FOUND_ROWS DISTINCT i.`seminar_id`
                                  FROM `seminar_inst` i
                                  JOIN `seminare` s ON (s.`Seminar_id` = i.`seminar_id`)
                                  WHERE i.`institut_id` IN (" . DBManager::get()->quote($institutes) . ")
                                    AND (s.`Name` LIKE {$prequery} OR s.`VeranstaltungsNummer` LIKE {$prequery})";
                    break;
                /*
                 * dozent, tutor, autor, user see files in their own courses,
                 * at institutes or in their personal file area.
                 */
                default:
                    $institutes = array_map(function ($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());

                    $mycourses = "SELECT SQL_CALC_FOUND_ROWS DISTINCT u.`Seminar_id`
                                  FROM `seminar_user` u
                                  JOIN `seminare` s ON (s.`Seminar_id` = u.`Seminar_id`)
                                  WHERE u.`user_id` = " . DBManager::get()->quote($GLOBALS['user']->id) . "
                                    AND (s.`Name` LIKE {$prequery} OR s.`VeranstaltungsNummer` LIKE {$prequery})";

                    if (Config::get()->DEPUTIES_ENABLE) {
                        $mycourses .= "
                            UNION
                            SELECT d.`range_id` AS Seminar_id
                            FROM `deputies` d
                                JOIN `seminare` s ON (s.`Seminar_id` = d.`range_id`)
                            WHERE d.`user_id` = " . DBManager::get()->quote($GLOBALS['user']->id) . "
                                AND (s.`Name` LIKE {$prequery} OR s.`VeranstaltungsNummer` LIKE {$prequery})";
                    }
            }

            $course_ids = DBManager::get()->fetchFirst($mycourses);

            // Fetch all files from relevant courses.
            return "SELECT SQL_CALC_FOUND_ROWS DISTINCT r.*, fo.`range_id`, f.`mime_type`
                    FROM `file_refs` r
                    JOIN `folders` fo ON (r.`folder_id` = fo.`id`)
                    JOIN `files` f ON (r.`file_id` = f.`id`)
                    WHERE fo.`range_id` IN (" . DBManager::get()->quote($course_ids) . ")
                      AND (r.`name` LIKE {$query} OR r.`description` LIKE {$query})
                    ORDER BY r.`chdate` DESC LIMIT " . $limit;

        } else {

            /*
             * Fetch the file_refs that match our search query,
             * search scope is defined by permission level.
             */
            switch ($GLOBALS['perm']->get_perm()) {
                // Roots see all files, no matter where.
                case 'root':
                    return "SELECT SQL_CALC_FOUND_ROWS DISTINCT r.*, fo.`range_id`, f.`mime_type`
                            FROM `file_refs` r
                            JOIN `folders` fo ON (r.`folder_id` = fo.`id`)
                            JOIN `files` f ON (r.`file_id` = f.`id`)
                            WHERE (r.`name` LIKE {$query} OR r.`description` LIKE {$query})
                            ORDER BY r.`chdate` DESC LIMIT " . $limit;

                /*
                 * Admins see files in courses at their own institutes,
                 * at their own institutes and their personal file area.
                 */
                case 'admin':
                    $institutes = array_map(function ($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());

                    return "SELECT SQL_CALC_FOUND_ROWS DISTINCT r.*, fo.`range_id`, f.`mime_type`
                            FROM `file_refs` r
                            JOIN `folders` fo ON (r.`folder_id` = fo.`id`)
                            JOIN `files` f ON (r.`file_id` = f.`id`)
                            WHERE (fo.`range_id` IN (
                                    SELECT `Seminar_id`
                                    FROM `seminar_inst`
                                    WHERE `institut_id` IN (" . DBManager::get()->quote($institutes) . ")
                                  )
                                  OR fo.`range_id` = " . DBManager::get()->quote($GLOBALS['user']->id) . "
                                  OR fo.`range_id` IN (" . DBManager::get()->quote($institutes) . ")
                              ) AND (r.`name` LIKE {$query} OR r.`description` LIKE {$query})
                            ORDER BY r.`chdate` DESC LIMIT " . $limit;
                /*
                 * dozent, tutor, autor, user see files in their own courses,
                 * at institutes or in their personal file area.
                 */
                default:
                    $mycourses = "SELECT `Seminar_id`
                                  FROM `seminar_user`
                                  WHERE `user_id` = " . DBManager::get()->quote($GLOBALS['user']->id);

                    if (Config::get()->DEPUTIES_ENABLE) {
                        $mycourses .= "
                            UNION
                            SELECT `range_id` AS Seminar_id
                            FROM `deputies`
                            WHERE `user_id` = " . DBManager::get()->quote($GLOBALS['user']->id);
                    }

                    return "SELECT SQL_CALC_FOUND_ROWS DISTINCT r.*, fo.`range_id`, f.`mime_type`
                            FROM `file_refs` r
                            JOIN `files` f ON (r.`file_id` = f.`id`)
                            JOIN `folders` fo
                              ON (r.`folder_id` = fo.`id`) AND (
                                  fo.`range_id` IN ({$mycourses})
                                  OR fo.`range_id` = ". DBManager::get()->quote($GLOBALS['user']->id) . "
                                  OR fo.`range_id` IN (SELECT Institut_id FROM Institute)
                              )
                            WHERE (r.`name` LIKE {$query} OR r.`description` LIKE {$query})
                            ORDER BY r.`chdate` DESC LIMIT " . $limit;
            }
        }

        return null;
    }

    /**
     * Returns an array of information for the found element
     * Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Icon according to file mimetype
     *
     * @param Array $fileref
     * @param $search
     * @return mixed
     */
    public static function filter($data, $search)
    {
        $fileref = FileRef::buildExisting($data);

        $folder = self::fromCache("folder/{$fileref->folder_id}", function () use ($fileref) {
            return Folder::find($fileref->folder_id)->getTypedFolder();
        });

        if (!$folder->isFileDownloadable($fileref, $GLOBALS['user']->id)) {
            return null;
        }

        if ($data['range_id'] == $GLOBALS['user']->id) {
            $range = $GLOBALS['user'];
            $range_path = '';
        } else {
            $range = self::fromCache("range/{$data['range_id']}", function () use ($data) {
                return Course::find($data['range_id'])
                    ?: Institute::find($data['range_id'])
                    ?: null;
            });
            $range_path = $range ? strtolower('/' . get_class($range)) : null;
        }

        $user = self::fromCache("user/{$fileref->user_id}", function () use ($fileref) {
            return User::findFull($fileref->user_id);
        });

        return [
            'id'         => $fileref->id,
            'name'       => self::mark($fileref->name, $search, true),
            'url'        => URLHelper::getURL(
                "dispatch.php/file/details/{$fileref->id}"
            ),
            'img'        => FileManager::getIconForMimeType($data['mime_type'])->asImagePath(),
            'additional' => self::mark($range ? $range->getFullname() : '', $search, false),
            'date'       => strftime('%x', $fileref['chdate']),
            'expand'     => URLHelper::getURL(
                "dispatch.php{$range_path}/files/index/{$fileref->folder_id}",
                ['cid' => $data['range_id']]
            ),
            'expandtext'  => _('In diesem Dateibereich suchen'),
            'user'       => $user ? $user->getFullname() : _('unbekannt')
        ];
    }

    /**
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE file_refs ADD FULLTEXT INDEX globalsearch (name)");
    }

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON file_refs");
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * @param string $searchterm what to search for?
     * @return URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }

    /**
     * Executes a fulltext (MATCH AGAINST) search in database for the given search term.
     *
     * @param string $search the term to search for.
     * @return string SQL query.
     */
    public static function getFulltextSearch($search)
    {
        // Filter for own courses
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $user = DBManager::get()->quote($GLOBALS['user']->id);
            $ownseminars = "JOIN seminar_user ON (dokumente.seminar_id = seminar_user.seminar_id AND seminar_user.user_id = $user) ";
        }

        // Now check if we got a seminar
        if (mb_strpos($search, '/') !== FALSE) {
            $args = explode('/', $search);
            $prequery = DBManager::get()->quote("%" . trim($args[0]) . "%");
            $query = DBManager::get()->quote("%" . trim($args[1]) . "%");
            $binary = DBManager::get()->quote('%' . join('%', preg_split('//u',
                        mb_strtoupper(trim($args[0])), null, PREG_SPLIT_NO_EMPTY)) . '%');
            $comp = "AND";
            return "SELECT SQL_CALC_FOUND_ROWS dokumente.*
                    FROM dokumente
                    JOIN seminare USING (seminar_id)
                    {$ownseminars}
                    WHERE (seminare.name LIKE BINARY {$binary} OR seminare.name LIKE {$prequery})
                      {$comp} dokumente.name LIKE {$query}
                    ORDER BY dokumente.chdate DESC LIMIT " . $limit;
        } else {
            $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));
            return "SELECT SQL_CALC_FOUND_ROWS dokumente.*
                    FROM dokumente IGNORE INDEX (chdate)
                    {$ownseminars}
                    WHERE MATCH(dokumente.name) AGAINST ($query IN BOOLEAN MODE)
                    ORDER BY dokumente.chdate DESC LIMIT " . $limit;
        }
    }
}
