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
    public function getResults($input, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0) {
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
        foreach ($presets as $name => $value) {
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

    private function getSQL() {
        $first_column = 'auth_user_md5.' . $this->avatarLike;
        switch ($this->search) {
            case "user":
                $sql = "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 " .
                        " LEFT JOIN user_info USING(user_id) " .
                        "WHERE ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) " .
                            "AND auth_user_md5.perms IN (:permission) ".
                            "AND auth_user_md5.user_id NOT IN(:exclude_user) " .
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            break;
            case "user_not_already_in_sem":
                $sql =  "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 " .
                        "LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm) " .
                        " LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id  " .
                        "WHERE su.user_id IS NULL AND ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) " .
                            "AND auth_user_md5.perms IN (:permission) ".
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            break;
            case "user_in_sem":
                $sql =  "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 " .
                        "JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm) " .
                        " LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id  " .
                        "WHERE ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) ".
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
            break;
            case "user_inst":
                $sql =  "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 LEFT JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id) " .
                        " LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id  " .
                        "WHERE ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) " .
                            "AND user_inst.Institut_id IN (:institute) " .
                            "AND user_inst.inst_perms IN (:permission) ".
                            "AND auth_user_md5.user_id NOT IN(:exclude_user) " .
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
           break;
           case "user_inst_not_already_in_sem":
                $sql =  "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 LEFT JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id) " .
                        "LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id AND status IN (:sem_perm) LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id  " .
                        "WHERE su.user_id IS NULL AND ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) " .
                            "AND user_inst.Institut_id IN (:institute) " .
                            "AND user_inst.inst_perms IN (:permission) ".
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
           break;
           case "user_not_already_in_sem_or_deputy":
                $sql =  "SELECT DISTINCT $first_column, CONCAT(Nachname, ', ', Vorname, ' (', username, ')') " .
                        "FROM auth_user_md5 LEFT JOIN seminar_user su ON su.user_id = auth_user_md5.user_id AND seminar_id=:seminar_id " .
                        " LEFT JOIN deputies d ON d.user_id = auth_user_md5.user_id AND range_id=:seminar_id LEFT JOIN user_info ON auth_user_md5.user_id = user_info.user_id " .
                        "WHERE su.user_id IS NULL AND d.user_id IS NULL AND ( " .
                            "CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE :input " .
                            "OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input " .
                            "OR auth_user_md5.username LIKE :input ) " .
                            "AND auth_user_md5.perms IN (:permission) ".
                        "ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname, auth_user_md5.username";
           break;
           default:
               throw new InvalidArgumentException("search parameter not valid");
        }
        return $sql;
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     * @return: path to this class
     */
    public function includePath() {
        return studip_relative_path(__FILE__);
    }
}
