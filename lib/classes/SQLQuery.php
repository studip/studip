<?php

/**
 * Class SQLQuery
 * This class is to MANAGE a query that is maybe more complex than a standard sql-query.
 * You can easily add filters, joins and additional select-parts.
 *
 * $query = SQLQuery::table("auth_user_md5")
 *              ->join("seminar_user", "seminar_user.user_id = auth_user_md5.user_id")
 *              ->join("seminar_inst", "seminar_inst.seminar_id = seminar_user.Seminar_id")
 *              ->where("seminar_inst.institut_id = :institut_id",array('institut_id' => $inst->getId()));
 * if (Request::get("status") {
 *      $query->where("auth_user_md5.status = :status", array('status' => Request::get("status"));
 * }
 * if ($query->count() <= 500) {
 *      $user_data = $query->fetchAll();
 * } else {
 *      PageLayout::postInfo(_("Geben Sie mehr Filter ein."));
 * }
 *
 *
 */
class SQLQuery
{
    public $settings = ['joins' => []];
    public $name = null;

    public static function table($table, $query_name = null)
    {
        $query = new self($table, $query_name);
        return $query;
    }

    /**
     * Constructor of the query. A main table is needed.
     * @param string $table : a database table
     * @param string name :
     */
    public function __construct($table, $query_name = null)
    {
        $this->settings['table'] = $table;
        $this->name = $query_name;
    }

    public function select($select, $statement = null)
    {
        if (!is_array($select)) {
            $select = $statement ? [$select => $statement] : [$select => ''];
        }
        foreach ($select as $alias => $statement) {
            $this->settings['select'][$alias] = $select;
        }
    }

    /**
     * Joins a table to the query. You can omit $table and just call join($tablename,
     * $on, $join) if you don't need an alias.
     * @param $alias : table-name or an alias.
     * @param null $table : optional table name. Use this if you want to use an alias.
     * @param string $on : any condition like "t1.user_id = t2.id"
     * @param string $join : type of joining "INNER JOIN" or "LEFT JOIN"
     * @return $this
     */
    public function join($alias, $table = null, $on = '', $join = 'INNER JOIN')
    {
        if (preg_match('/[\s=]/', $table)) {
            // user left away the $table var and shifted the other variables:
            $join = $on;
            $on = $table;
            $table = null;
        }
        $this->settings['joins'][$alias] = [];
        if ($table) {
            $this->settings['joins'][$alias]['table'] = $table;
        }
        if ($on) {
            $this->settings['joins'][$alias]['on'] = $on;
        }
        if ($join) {
            $this->settings['joins'][$alias]['join'] = $join;
        }
        return $this;
    }

    /**
     * Adds a condition to the query. Any conditions will get concatenated by AND.
     * @param $name : the name of the condition. Use any name. It will be treated as an identifier.
     * @param null $condition
     * @param array $parameter
     * @return $this
     */
    public function where($name, $condition = null, $parameter = [])
    {
        if ($condition === null && isset($this->settings['where'][$name])) {
            unset($this->settings['where'][$name]);
        } elseif ($parameter === null && $condition !== null) {
            $this->settings['where'][$name] = $name;
            $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $condition);
        } elseif ($condition === null)  {
            $this->settings['where'][md5($name)] = $name;
        } else {
            $this->settings['where'][$name] = $condition;
            $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $parameter);
        }
        return $this;
    }

    public function having($name, $condition = null, $parameter = [])
    {
        if ($condition === null && isset($this->settings['having'][$name])) {
            unset($this->settings['having'][$name]);
        } elseif ($parameter === null && $condition !== null) {
            $this->settings['having'][$name] = $name;
            $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $condition);
        } elseif ($condition === null)  {
            $this->settings['having'][md5($name)] = $name;
        } else {
            $this->settings['having'][$name] = $condition;
            $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $parameter);
        }
        return $this;
    }

    /**
     * Sets one or more parameter for this query.
     * @param array|string $param : associative array or string
     * @param null|string $value : value of parameter when $param is a string
     */
    public function parameter($param, $value = null)
    {
        if (is_array($param)) {
            $this->settings['parameter'] = array_merge((array) $this->settings['parameter'], $param);
        } else {
            $this->settings['parameter'][$param] = $value;
        }
    }

    /**
     * Removes a formerly defined where condition identified by its name.
     * @param $name : the name of the where-condition that was defined in where-method..
     * @return $this
     */
    public function removeWhereCondition($name)
    {
        unset($this->settings['where'][$name]);
        return $this;
    }

    /**
     * Sets the grouping of the resultset.
     * @param string $clause : the clause to sort after like "auth_user_md5.user_id"
     * @return $this
     */
    public function groupBy($clause)
    {
        $this->settings['groupby'] = $clause;
        return $this;
    }

    /**
     * Sets the order of the resultset.
     * @param string $clause : the clause to sort after like "Vorname ASC, position DESC"
     * @return $this
     */
    public function orderBy($clause)
    {
        $this->settings['order'] = $clause;
        return $this;
    }

    /**
     * Limits the resultset of the query.
     * @param integer $start
     * @param null|integer $end
     * @return $this
     */
    public function limit($start, $end = null)
    {
        if ($end === null) {
            $end = $start;
            $start = 0;
        }
        $this->settings['limit'] = [$start, $end];
        return $this;
    }

    /**
     * Fetches the number of entries of the resultset.
     * @return integer.
     */
    public function count()
    {
        NotificationCenter::postNotification('SQLQueryWillExecute', $this);
        $sql = "SELECT COUNT(*) FROM (
                    SELECT `{$this->settings['table']}`.*
                    {$this->getQuery()}
                ) AS counter_table";
        $statement = DBManager::get()->prepare($sql);
        $statement->execute((array) $this->settings['parameter']);
        NotificationCenter::postNotification('SQLQueryDidExecute', $this);

        return (int) $statement->fetchColumn();
    }

    /**
     * Fetches the whole resultset as an array of associative arrays. If you define
     * a sorm_class the result will be an array of the sorm-objects.
     * @param $sorm_class_or_column : column name, a class of SimpleORMap or null for associative array.
     * @return array of arrays or array of objects or array of values.
     */
    public function fetchAll($sorm_class_or_column = null)
    {
        NotificationCenter::postNotification('SQLQueryWillExecute', $this);

        if (is_string($sorm_class_or_column) && !is_subclass_of($sorm_class_or_column, "SimpleORMap")) {
            $sql = "SELECT `{$this->settings['table']}`.`{$sorm_class_or_column}` ";
        } else {
            $sql = "SELECT `{$this->settings['table']}`.* ";
        }

        foreach ((array) $this->settings['select'] as $alias => $statement) {
            $sql .= $statement ? "{$statement} AS {$alias} " : $alias;
        }

        $sql .= $this->getQuery();

        $statement = DBManager::get()->prepare($sql);
        $statement->execute((array) $this->settings['parameter']);

        NotificationCenter::postNotification('SQLQueryDidExecute', $this);

        if (is_string($sorm_class_or_column) && !is_subclass_of($sorm_class_or_column, "SimpleORMap")) {
            return $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        $alldata = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$sorm_class_or_column) {
            return $alldata;
        }

        return array_map("{$sorm_class_or_column}::buildExisting", $alldata);
    }

    /**
     * Shows the query that would be executed in the method fetchAll
     * @return string : sql query
     */
    public function show()
    {
        $sql = "SELECT `{$this->settings['table']}`.* ";

        foreach ((array) $this->settings['select'] as $alias => $statement) {
            $sql .= $statement ? "{$statement} AS {$alias} " : $alias;
        }

        $sql .= $this->getQuery();
        return $sql;
    }

    /*******************************************************************************
     *                            protected and private                            *
     *******************************************************************************/

    /**
     * Constructs a query string for a prepared statement without the SELECT part.
     * @return string
     */
    protected function getQuery()
    {
        $sql = "FROM `{$this->settings['table']}` ";
        foreach ($this->settings['joins'] as $alias => $joindata) {
            $table = isset($joindata['table']) ? "{$joindata['table']} AS {$alias}" : $alias;
            $on = isset($joindata['on']) ? " ON ({$joindata['on']})" : '';
            $sql .= " " . (isset($joindata['join']) ? $joindata['join'] : 'INNER JOIN') . " {$table}{$on} ";
        }
        if ($this->settings['where']) {
            $sql .= "WHERE (" . implode(") AND (", $this->settings['where']) . ") ";
        }
        if ($this->settings['groupby']) {
            $sql .= "GROUP BY {$this->settings['groupby']} ";
        }
        if ($this->settings['having']) {
            $sql .= "HAVING (" . implode(") AND (", $this->settings['having']) . ") ";
        }

        if ($this->settings['order']) {
            $sql .= "ORDER BY {$this->settings['order']} ";
        }
        if ($this->settings['limit']) {
            $sql .= "LIMIT ". (int) $this->settings['limit'][0] . ", " . (int) $this->settings['limit'][1] . " ";
        }
        return $sql;
    }

    /**
     * Fetches the primary key of the table and returns it as an array.
     * @return array : primary key as array("column1", "column2")
     */
    protected function getPrimaryKey()
    {
        $query = "SHOW COLUMNS FROM `{$this->settings['table']}`
                  WHERE `Key` = 'PRI'";
        $statement = DBManager::get()->exec($query);

        $pk = [];
        while ($rs = $statement->fetch(PDO::FETCH_ASSOC)) {
            $pk[] = "`{$this->settings['table']}`.`{$rs['Field']}`";
        }
        return $pk;
    }
}
