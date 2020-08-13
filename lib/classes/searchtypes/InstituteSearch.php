<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or any later version
*/
class InstituteSearch extends SearchType
{
    protected $excluded = [];

    public function __construct(array $excluded = [])
    {
        $this->excluded = $excluded;
    }

    /**
     * title of the search like "search for courses" or just "courses"
     * @return string
     */
    public function getTitle()
    {
        return _('Einrichtungen suchen');
    }

    /**
     * Returns the results to a given keyword. To get the results is the
     * job of this routine and it does not even need to come from a database.
     * The results should be an array in the form
     * array (
     *   array($key, $name),
     *   array($key, $name),
     *   ...
     * )
     * where $key is an identifier like user_id and $name is a displayed text
     * that should appear to represent that ID.
     * @param keyword: string
     * @param array $contextual_data an associative array with more variables
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     * @return array
     */
    public function getResults($keyword, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0)
    {
        if (!$GLOBALS['perm']->have_perm('admin')) {
            return [];
        }

        $parameters = [];
        $parameters[':input']    = $keyword;
        $parameters[':excluded'] = $this->excluded ?: '';

        if ($GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT `Institut_id`, `Name`
                      FROM `Institute`
                      WHERE `Name` LIKE CONCAT('%', :input, '%')
                        AND `Institut_id` NOT IN (:excluded)
                      ORDER BY `Name` ASC";
        } else {
            $query = "SELECT `Institut_id`, `Name`
                      FROM `Institute_id`
                      JOIN `user_inst` USING (`Institut_id`)
                      WHERE `user_id` = :user_id
                        AND `Name` LIKE CONCAT('%', :input, '%')
                        AND `Institut_id` NOT IN (:excluded)
                        AND `inst_perms` = 'admin'
                      ORDER BY `Name` ASC";
            $parameters[':user_id'] = $GLOBALS['user']->id;
        }

        $query .= " LIMIT {$offset}, {$limit}";

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_NUM);
     }

    /**
     * Returns the path to this file, so that this class can be autoloaded and is
     * always available when necessary.
     * Should be: "return __file__;"
     *
     * @return string   path to this file
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
