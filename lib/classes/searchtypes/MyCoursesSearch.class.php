<?php
/**
 * MyCoursesSearch.class.php
 * Search only in own courses.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class MyCoursesSearch extends StandardSearch
{
    public $search;

    private $perm_level;
    private $parameters;
    protected $additional_sql_conditions;

    /**
     *
     * @param string $search
     *
     * @param string $perm_level
     *
     * @param string $additional_sql_conditions An additional SQL snippet
     *      consisting of conditions. This snippet is appended to the
     *      default conditions.
     *
     * @return void
     */
    public function __construct(
        $search,
        $perm_level = 'dozent',
        $parameters = [],
        $additional_sql_conditions = ''
    )
    {
        $this->avatarLike = $this->search = $search;
        $this->perm_level = $perm_level;
        $this->parameters = $parameters;
        $this->additional_sql_conditions = $additional_sql_conditions;
        $this->sql = $this->getSQL();
    }


    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        return _('Veranstaltung suchen');
    }

    /**
     * returns the results of a search
     * Use the contextual_data variable to send more variables than just the input
     * to the SQL. QuickSearch for example sends all other variables of the same
     * <form>-tag here.
     * @param input string: the search-word(s)
     * @param contextual_data array: an associative array with more variables
     * @param limit int: maximum number of results (default: all)
     * @param offset int: return results starting from this row (default: 0)
     * @return array: array(array(), ...)
     */
    public function getResults($input, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0)
    {
        $db = DBManager::get();
        $sql = $this->getSQL();
        if (!$sql) {
            return [];
        }
        if ($offset || $limit != PHP_INT_MAX) {
            $sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
        }
        foreach ($this->parameters + $contextual_data as $name => $value) {
            if ($name !== "input" && mb_strpos($sql, ":".$name) !== false) {
                if (is_array($value)) {
                    if (count($value)) {
                        $sql = str_replace(":".$name, implode(',', array_map([$db, 'quote'], $value)), $sql);
                    } else {
                        $sql = str_replace(":".$name, "''", $sql);
                    }
                } else {
                    $sql = str_replace(":".$name, $db->quote($value), $sql);
                }
            }
        }
        $statement = $db->prepare($sql, [PDO::FETCH_NUM]);
        $data = [];
        $data[":input"] = "%".$input."%";
        $statement->execute($data);
        $results = $statement->fetchAll();
        return $results;
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL()
    {
        $semnumber = Config::get()->IMPORTANT_SEMNUMBER;
        $semester_text = "CONCAT(
            '(',
            IF(semester_data.semester_id IS NULL, '" . _('unbegrenzt') . "',
                GROUP_CONCAT(semester_data.`name` SEPARATOR ', ')),
            ')'
        )";

        switch ($this->perm_level) {
            // Roots see everything, everywhere.
            case 'root':
                $query = "SELECT DISTINCT s.`Seminar_id`, CONCAT_WS(' ', s.`VeranstaltungsNummer`, s.`Name`, " . $semester_text . ")
                    FROM `seminare` s
                        LEFT JOIN semester_courses ON (s.Seminar_id = semester_courses.course_id)
                        LEFT JOIN `semester_data` ON (semester_data.semester_id = semester_courses.semester_id)
                    WHERE (s.`VeranstaltungsNummer` LIKE :input
                            OR s.`Name` LIKE :input)
                        AND s.`status` NOT IN (:semtypes)
                        AND s.`Seminar_id` NOT IN (:exclude)
                        AND semester_data.`semester_id` IN (:semesters)
                    ";
                if ($this->additional_sql_conditions) {
                    $query .= ' AND ' . $this->additional_sql_conditions . ' ';
                }
                $query .= " GROUP BY s.Seminar_id ";
                if ($semnumber) {
                    $query .= " ORDER BY MAX(semester_data.`beginn`) DESC, s.`VeranstaltungsNummer`, s.`Name`";
                } else {
                    $query .= " ORDER BY MAX(semester_data.beginn) DESC, s.`VeranstaltungsNummer`, s.`Name`";
                }
                return $query;
            // Admins see everything at their assigned institutes.
            case 'admin':
                $sem_inst = Config::get()->ALLOW_ADMIN_RELATED_INST ? 'si' : 's';
                $query = "SELECT DISTINCT s.`Seminar_id`, CONCAT_WS(' ', s.`VeranstaltungsNummer`, s.`Name`, " . $semester_text . ")
                    FROM `seminare` s
                        JOIN `seminar_inst` si USING (Seminar_id)
                        LEFT JOIN semester_courses ON (s.Seminar_id = semester_courses.course_id)
                        LEFT JOIN `semester_data` ON (semester_data.semester_id = semester_courses.semester_id)
                    WHERE (s.`VeranstaltungsNummer` LIKE :input
                            OR s.`Name` LIKE :input)
                        AND s.`status` NOT IN (:semtypes)
                        AND $sem_inst.`institut_id` IN (:institutes)
                        AND s.`Seminar_id` NOT IN (:exclude)
                        AND semester_data.`semester_id` IN (:semesters)";
                if ($this->additional_sql_conditions) {
                    $query .= ' AND ' . $this->additional_sql_conditions . ' ';
                }
                $query .= " GROUP BY s.Seminar_id ";
                if ($semnumber) {
                    $query .= " ORDER BY MAX(semester_data.`beginn`) DESC, s.`VeranstaltungsNummer`, s.`Name`";
                } else {
                    $query .= " ORDER BY MAX(semester_data.`beginn`) DESC, s.`Name`";
                }
                return $query;
            // non-admins search all their administrable courses.
            default:
                $query = "SELECT DISTINCT s.`Seminar_id`, CONCAT_WS(' ', s.`VeranstaltungsNummer`, s.`Name`, " . $semester_text . "),
                        s.`VeranstaltungsNummer` AS num, s.`Name`, MAX(semester_data.beginn) as beginn
                    FROM `seminare` s
                        JOIN `seminar_user` su ON (s.`Seminar_id`=su.`Seminar_id`)
                        LEFT JOIN semester_courses ON (s.Seminar_id = semester_courses.course_id)
                        LEFT JOIN `semester_data` ON (semester_data.semester_id = semester_courses.semester_id)
                    WHERE (s.`VeranstaltungsNummer` LIKE :input
                            OR s.`Name` LIKE :input)
                        AND su.`user_id` = :userid
                        AND su.`status` IN ('dozent','tutor')
                        AND s.`status` NOT IN (:semtypes)
                        AND s.`Seminar_id` NOT IN (:exclude)
                        AND semester_data.`semester_id` IN (:semesters)";
                if (Config::get()->DEPUTIES_ENABLE) {
                    $query .= " UNION
                        SELECT DISTINCT s.`Seminar_id`, CONCAT_WS(' ', s.`VeranstaltungsNummer`, ' ', s.`Name`, " . $semester_text . "),
                            s.`VeranstaltungsNummer` AS num, s.`Name`, MAX(semester_data.beginn) AS beginn
                        FROM `seminare` s
                            JOIN `deputies` d ON (s.`Seminar_id` = d.`range_id`)
                            LEFT JOIN semester_courses ON (s.Seminar_id = semester_courses.course_id)
                            LEFT JOIN `semester_data` ON (semester_data.semester_id = semester_courses.semester_id)
                        WHERE (s.`VeranstaltungsNummer` LIKE :input
                                OR s.`Name` LIKE :input)
                            AND d.`user_id` = :userid
                            AND s.`Seminar_id` NOT IN (:exclude)
                            AND semester_data.`semester_id` IN (:semesters)";
                }
                if ($this->additional_sql_conditions) {
                    $query .= ' AND ' . $this->additional_sql_conditions . ' ';
                }
                $query .= " GROUP BY s.Seminar_id";
                if ($semnumber) {
                    $query .= " ORDER BY beginn DESC, num, `Name`";
                } else {
                    $query .= " ORDER BY beginn DESC, `Name`";
                }

                return $query;
        }
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
