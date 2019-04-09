<?php
# Lifter010: TODO
/**
 * SQLSearch.class.php - Class of type SearchType used for searches with QuickSearch
 *
 * Long description for file (if any)...
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Class of type SearchType used for searches with QuickSearch
 * (lib/classes/QuickSearch.class.php). You can search with a sql-syntax in the
 * database. You just need to give in a query like for a PDB-prepare statement
 * and at least the variable ":input" in the query (the :input will be replaced
 * with the input of the QuickSearch userinput.
 *  [code]
 *  $search = new SQLSearch("SELECT username, Nachname "
 *      "FROM auth_user_md5 " .
 *      "WHERE Nachname LIKE :input ", _("Nachname suchen"), "username");
 *  [/code]
 *
 * @author Rasmus Fuhse
 *
 */

class SQLSearch extends SearchType
{

    protected $sql;
    protected $avatarLike;
    protected $title;
    public $extendedLayout = false;

    /**
     *
     * @param string $query SQL with at least ":input" as parameter
     * @param string $title
     * @param string $avatarLike
     * in this search. array("input_name" => "placeholder_in_sql_query")
     *
     * @return void
     */
    public function __construct($query, $title = "", $avatarLike = "")
    {
        $this->sql = $query;
        $this->title = $title;
        $this->avatarLike = $avatarLike;
    }

    /**
     * returns an object of type SQLSearch with parameters to constructor
     *
     * @param string $query SQL with at least ":input" as parameter
     * @param string $title
     * @param string $avatarLike
     * in this search. array("input_name" => "placeholder_in_sql_query")
     *
     * @return SQLSearch
     */
    static public function get()
    {
        $class = get_called_class();
        $ref = new ReflectionClass($class);
        return $ref->newInstanceArgs(func_get_args());
    }

    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * returns an adress of the avatar of the searched item (if avatar enabled)
     *
     * @param string $id id of the item which can be username, user_id, Seminar_id or Institut_id
     * @param string $size enum(NORMAL, SMALL, MEDIUM): size of the avatar-image
     *
     * @return string adress of an image
     */
    public function getAvatar($id)
    {
        switch ($this->avatarLike) {
            case "username":
                return Avatar::getAvatar(get_userid($id), $id)->getURL(Avatar::MEDIUM);
            case "user_id":
                return Avatar::getAvatar($id)->getURL(Avatar::MEDIUM);
            case "Seminar_id":
            case "Arbeitsgruppe_id":
                return CourseAvatar::getAvatar($id)->getURL(Avatar::SMALL);
            case "Institut_id":
                return InstituteAvatar::getAvatar($id)->getURL(Avatar::SMALL);
        }
    }

    /**
     * returns an html tag of the image of the searched item (if avatar enabled)
     *
     * @param string $id id of the item which can be username, user_id, Seminar_id or Institut_id
     * @param constant $size enum(NORMAL, SMALL, MEDIUM): size of the avatar
     *
     * @return string like "<img src="...avatar.jpg" ... >"
     */
    public function getAvatarImageTag($id, $size = Avatar::SMALL, $options = [])
    {
        switch ($this->avatarLike) {
            case "username":
                return Avatar::getAvatar(get_userid($id), $id)->getImageTag($size, $options);
            case "user_id":
                return Avatar::getAvatar($id)->getImageTag($size, $options);
            case "Seminar_id":
            case "Arbeitsgruppe_id":
                return CourseAvatar::getAvatar($id)->getImageTag($size, $options);
            case "Institut_id":
                return InstituteAvatar::getAvatar($id)->getImageTag($size, $options);
        }
    }

    /**
     * returns the results of a search
     * Use the contextual_data variable to send more variables than just the input
     * to the SQL. QuickSearch for example sends all other variables of the same
     * <form>-tag here.
     *
     * @param string $input the search-word(s)
     * @param array $contextual_data an associative array with more variables
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     *
     * @return array  array(array(), ...)
     */
    public function getResults($input, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0)
    {
        $db = DBManager::get();
        $sql = $this->sql;
        if ($offset || $limit != PHP_INT_MAX) {
            $sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
        }
        $statement = $db->prepare($sql, [PDO::FETCH_NUM]);
        $data = [];
        if (is_array($contextual_data)) {
            foreach ($contextual_data as $name => $value) {
                if ($name !== "input" && mb_strpos($sql, ":".$name) !== false) {
                    $data[":".$name] = $value;
                }
            }
        }
        $data[":input"] = "%".$input."%";
        $statement->execute($data);
        $results = $statement->fetchAll();
        return $results;
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return string path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
