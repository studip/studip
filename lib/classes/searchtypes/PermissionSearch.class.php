<?php
# Lifter010: TODO

/*
 * Copyright (C) 2010 - Thomas Hackl <thomas.hackl@uni-passau.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Class of type SearchType used for searches with QuickSearch
 * (lib/classes/QuickSearch.class.php). You can search for people with a given
 * Stud.IP permission level, either globally or at an institute.
 *
 * @author Thomas Hackl
 *
 */

class PermissionSearch extends SQLSearch {

    private $search;
    private $presets;

    /**
     *
     * @param string $query: SQL with at least ":input" as parameter
     * @param array $presets: variables from the same form that should be used
     * in this search. array("input_name" => "placeholder_in_sql_query")
     * @return void
     */
    public function __construct($search, $title = "", $avatarLike = "user_id", $presets = []) {
        $this->search = $search;
        $this->presets = $presets;
        $this->title = $title;
        $this->avatarLike = in_array($avatarLike, words('user_id, username')) ? $avatarLike : 'user_id';
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
        if ($offset || $limit != PHP_INT_MAX) {
            $sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
        }
        if (is_callable($this->presets, true)) {
            $presets = call_user_func($this->presets, $this, $contextual_data);
        } else {
            $presets = $this->presets + $contextual_data;
        }

        $data = $this->getDefaultData();
        $data[':input'] = "%{$input}%";

        foreach ($presets as $name => $value) {
            if (is_array($value) && !$value) {
                $value = '';
            }

            if ($name !== 'input' && mb_strpos($sql, ":{$name}") !== false) {
                $data[":{$name}"] = $value;
            }
        }

        $statement = $db->prepare($sql);
        $statement->execute($data);
        $results = $statement->fetchAll();
        return $results;
    }

    private function getSQL()
    {
        $first_column = "auth_user_md5.{$this->avatarLike}";

        // Respect user visibility setting
        if ($GLOBALS['user']->perms === 'root') {
            // Root may find everyone
            $visibility_condition = '1';
        } else {
            $visibility_condition = "auth_user_md5.visible NOT IN ('never')";
            if (Config::get()->DOZENT_ALWAYS_VISIBLE) {
                $visibility_condition .= " OR auth_user_md5.perms = 'dozent'";
            }
            $visibility_condition = "({$visibility_condition})";
        }

        switch ($this->search) {
            case 'user':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        LEFT JOIN user_info USING (user_id)
                        WHERE (
                            CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                            OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                            OR auth_user_md5.username LIKE :input
                          )
                          AND auth_user_md5.perms IN (:permission)
                          AND auth_user_md5.user_id NOT IN (:exclude_user)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            case 'user_not_already_in_sem':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm)
                        LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                        WHERE su.user_id IS NULL
                          AND (
                              CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                              OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                              OR auth_user_md5.username LIKE :input
                          )
                          AND auth_user_md5.perms IN (:permission)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            case 'user_in_sem':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm)
                        LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                        WHERE (
                            CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                            OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                            OR auth_user_md5.username LIKE :input
                          )
                          AND auth_user_md5.user_id NOT IN (:exclude_user)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            break;
            case 'user_inst':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        LEFT JOIN user_inst ON user_inst.user_id = auth_user_md5.user_id
                        LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                        WHERE (
                            CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                            OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                            OR auth_user_md5.username LIKE :input
                          )
                          AND user_inst.Institut_id IN (:institute)
                          AND user_inst.inst_perms IN (:permission)
                          AND auth_user_md5.user_id NOT IN (:exclude_user)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
           case 'user_inst_not_already_in_sem':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        LEFT JOIN user_inst ON user_inst.user_id = auth_user_md5.user_id
                        LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm)
                        LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                        WHERE su.user_id IS NULL
                          AND (
                              CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                              OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                              OR auth_user_md5.username LIKE :input
                          )
                          AND user_inst.Institut_id IN (:institute)
                          AND user_inst.inst_perms IN (:permission)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
           case 'user_not_already_in_sem_or_deputy':
                return "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')')
                        FROM auth_user_md5
                        LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id
                        LEFT JOIN deputies d ON d.user_id = auth_user_md5.user_id AND range_id=:seminar_id
                        LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id
                        WHERE su.user_id IS NULL
                          AND d.user_id IS NULL
                          AND (
                              CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                              OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                              OR auth_user_md5.username LIKE :input
                          )
                          AND auth_user_md5.perms IN (:permission)
                          AND {$visibility_condition}
                        ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
        }

        // No search type matched?
        throw new InvalidArgumentException('search parameter not valid');
    }

    private function getDefaultData()
    {
        $data = [];
        if (in_array($this->search, ['user', 'user_in_sem', 'user_inst'])) {
            $data[':exclude_user'] = '';
        }
        if (in_array($this->search, ['user_not_already_in_sem', 'user_inst_not_already_in_sem'])) {
            $data[':sem_perm'] = ['autor', 'tutor', 'dozent'];
        }
        return $data;
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     * @return: path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
