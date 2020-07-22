<?php
/**
 * StudipPDO.class.php - Stud.IP PDO class.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @category    Stud.IP
 */

/**
 * This is a special variant of the standard PDO class that does
 * not allow multiple statement execution.
 */
class StudipPDO extends PDO
{
    const PARAM_ARRAY = 100;
    const PARAM_COLUMN = 101;

    // Counter for the queries sent to the database
    public $query_count = 0;

    /**
     * Verifies that the given SQL query only contains a single statement.
     *
     * @param string    SQL statement to check
     *
     * @throws PDOException when the query contains multiple statements
     */
    protected function verify($statement)
    {
        if (mb_strpos($statement, ';') !== false) {
            if (preg_match('/;\s*\S/', self::replaceStrings($statement))) {
                throw new PDOException('multiple statement execution not allowed');
            }
        }

        // Count executed queries (this is placed here since this is the only
        // method that is executed on every call to the database)
        $this->query_count += 1;
    }

    /**
     * Replaces all string literals in the statement with placeholders.
     *
     * @param string    SQL statement
     *
     * @return string modified SQL statement
     */
    protected static function replaceStrings($statement)
    {
        $count = mb_substr_count($statement, '"') + mb_substr_count($statement, "'") + mb_substr_count($statement, '\\');

        // use fast preg_replace() variant if possible
        if ($count < 1000) {
            $result = preg_replace('/"(""|\\\\.|[^\\\\"]+)*"|\'(\'\'|\\\\.|[^\\\\\']+)*\'/s', '?', $statement);
        }

        if (!isset($result)) {
            // split string into parts at quotes and backslash
            $parts = preg_split('/([\\\\"\'])/', $statement, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $result = '';

            for ($part = current($parts); $part !== false; $part = next($parts)) {
                // inside quotes, "" is ", '' is ' and \x is x
                if ($quote_chr !== null) {
                    if ($part === $quote_chr) {
                        $part = next($parts);

                        if ($part !== $quote_chr) {
                            // backtrack and terminate string
                            prev($parts);
                            $result .= '?';
                            $quote_chr = null;
                        }
                    } elseif ($part === '\\') {
                        // skip next part
                        next($parts);
                    }
                } elseif ($part === "'" || $part === '"') {
                    $quote_chr = $part;
                    $saved_pos = key($parts);
                } else {
                    $result .= $part;
                }
            }

            if ($quote_chr !== null) {
                // unterminated quote: copy to end of string
                $result .= implode(array_slice($parts, $saved_pos));
            }
        }

        return $result;
    }

    /**
     * Quotes the given value in a form appropriate for the type.
     * If no explicit type is given, the value's PHP type is used.
     *
     * @param string    PHP value to quote
     * @param int       parameter type (e.g. PDO::PARAM_STR)
     *
     * @return string quoted SQL string
     */
    public function quote($value, $type = null)
    {
        if (!isset($type)) {
            if (is_null($value)) {
                $type = PDO::PARAM_NULL;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_array($value)) {
                $type = self::PARAM_ARRAY;
            } else {
                $type = PDO::PARAM_STR;
            }
        }

        switch ($type) {
            case PDO::PARAM_NULL:
                return 'NULL';
            case PDO::PARAM_BOOL:
                return $value ? '1' : '0';
            case PDO::PARAM_INT:
                return (int) $value;
            case self::PARAM_ARRAY:
                return is_array($value) && count($value) ? join(',', array_map(array($this, 'quote'), $value)) : 'NULL';
            case self::PARAM_COLUMN:
                return preg_replace('/\\W/', '', $value);
            default:
                return parent::quote($value);
        }
    }

    /**
     * Executes an SQL statement and returns the number of affected rows.
     *
     * @param string    SQL statement
     *
     * @return int number of affected rows
     */
    public function exec($statement)
    {
        $this->verify($statement);

        return parent::exec($statement);
    }

    /**
     * Executes an SQL statement, returning a result set as a statement object.
     *
     * @param string    SQL statement
     * @param int       fetch mode (optional)
     * @param mixed     fetch mode parameter (see PDOStatement::setFetchMode)
     * @param mixed     fetch mode parameter (see PDOStatement::setFetchMode)
     *
     * @return object PDOStatement object
     */
    public function query($statement, $mode = null, $arg1 = null, $arg2 = null)
    {
        $this->verify($statement);

        if (isset($mode)) {
            $stmt = parent::query($statement, $mode, $arg1, $arg2);
        } else {
            $stmt = parent::query($statement);
        }

        $studip_stmt = new StudipPDOStatement($this, $statement, array());
        $studip_stmt->setStatement($stmt);

        return $studip_stmt;
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string    SQL statement
     *
     * @return object PDOStatement object
     */
    public function prepare($statement, $driver_options = array())
    {
        $this->verify($statement);

        return new StudipPDOStatement($this, $statement, $driver_options);
    }

    /**
     * This method is intended only for use by the StudipPDOStatement class.
     *
     * @param string    SQL statement
     *
     * @return object PDOStatement object
     */
    public function prepareStatement($statement, $driver_options = array())
    {
        return parent::prepare($statement, $driver_options);
    }

    /**
     * Executes sql statement with given parameters,
     * returns number of affected rows, use only for INSERT,UPDATE etc.
     *
     * @param string $statement        SQL statement to execute
     * @param array  $input_parameters parameters for statement
     *
     * @return int number of affected rows
     */
    public function execute($statement, $input_parameters = null)
    {
        $st = $this->prepare($statement);
        $ok = $st->execute($input_parameters);
        if ($ok === true) {
            return $st->rowCount();
        }
    }

    /**
     * Executes sql statement with given parameters, and fetch results
     * as sequential array, each row as associative array
     * optionally apply given callable on each row, with current row and key as parameter.
     *
     * @param string   $statement        SQL statement to execute
     * @param array    $input_parameters parameters for statement
     * @param callable $callable         callable to be applied to each of the rows
     *
     * @return array result set as array of assoc arrays
     */
    public function fetchAll($statement, $input_parameters = null, $callable = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        if (is_callable($callable)) {
            $data = array();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($st as $key => $row) {
                $data[$key] = call_user_func($callable, $row, $key);
            }
        } else {
            $data = $st->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    /**
     * Executes sql statement with given parameters, and fetch only
     * the values from first column as sequential array
     * optionally apply given callable on each row, with current value and key as parameter.
     *
     * @see StudipPDOStatement::fetchFirst()
     *
     * @param string   $statement        SQL statement to execute
     * @param array    $input_parameters parameters for statement
     * @param callable $callable         callable to be applied to each of the rows
     *
     * @return array result set
     */
    public function fetchFirst($statement, $input_parameters = null, $callable = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        $data = $st->fetchFirst();
        if (is_callable($callable)) {
            foreach ($data as $key => $row) {
                $data[$key] = call_user_func($callable, $row, $key);
            }
        }

        return $data;
    }

    /**
     * Executes sql statement with given parameters, and fetch results
     * as associative array, first columns value is used as a key, the others are grouped
     * optionally apply given callable on each grouped row, with current row and key as parameter
     * if no callable is given, 'current' is used, to return the first entry of the grouped row.
     *
     * @see StudipPDOStatement::fetchGrouped()
     *
     * @param string   $statement        SQL statement to execute
     * @param array    $input_parameters parameters for statement
     * @param callable $callable         callable to be applied to each of the rows
     *
     * @return array result set
     */
    public function fetchGrouped($statement, $input_parameters = null, $callable = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        $data = $st->fetchGrouped(PDO::FETCH_ASSOC, is_null($callable) ? 'current' : null);
        if (is_callable($callable)) {
            foreach ($data as $key => $row) {
                $data[$key] = call_user_func($callable, $row, $key);
            }
        }

        return $data;
    }

    /**
     * Executes sql statement with given parameters, and fetch results
     * as associative array, first columns value is used as a key, the other one is grouped
     * use only when selecting 2 columns
     * optionally apply given callable on each grouped row, with current row and key as parameter.
     *
     * @see StudipPDOStatement::fetchGroupedPairs()
     *
     * @param string   $statement        SQL statement to execute
     * @param array    $input_parameters parameters for statement
     * @param callable $callable         callable to be applied to each of the rows
     *
     * @return array result set
     */
    public function fetchGroupedPairs($statement, $input_parameters = null, $callable = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        $data = $st->fetchGroupedPairs();
        if (is_callable($callable)) {
            foreach ($data as $key => $row) {
                $data[$key] = call_user_func($callable, $row, $key);
            }
        }

        return $data;
    }

    /**
     * Executes sql statement with given parameters, and fetch results
     * as associative array, first columns value is used as a key, the other one as the value
     * use only when selecting 2 columns
     * optionally apply given callable on each grouped row, with current row and key as parameter.
     *
     * @see StudipPDOStatement::fetchGroupedPairs()
     *
     * @param string   $statement        SQL statement to execute
     * @param array    $input_parameters parameters for statement
     * @param callable $callable         callable to be applied to each of the rows
     *
     * @return array result set
     */
    public function fetchPairs($statement, $input_parameters = null, $callable = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        $data = $st->fetchPairs();
        if (is_callable($callable)) {
            foreach ($data as $key => $row) {
                $data[$key] = call_user_func($callable, $row, $key);
            }
        }

        return $data;
    }

    /**
     * Executes sql statement with given parameters, and fetch only the first row
     * as associative array.
     *
     * @see StudipPDOStatement::fetchOne()
     *
     * @param string $statement        SQL statement to execute
     * @param array  $input_parameters parameters for statement
     *
     * @return array first row of result set
     */
    public function fetchOne($statement, $input_parameters = null)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);

        return $st->fetchOne();
    }

    /**
     * Executes sql statement with given parameters, and fetch only the value of one column
     * third param denotes the column, zero indexed.
     *
     * @param string $statement        SQL statement to execute
     * @param array  $input_parameters parameters for statement
     * @param int    $column           number of column to fetch
     *
     * @return string value of chosen column
     */
    public function fetchColumn($statement, $input_parameters = null, $column = 0)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);

        return $st->fetchColumn($column);
    }
}

/**
 * This is a "fake" PDOStatement implementation that behaves mostly like
 * a real statement object, but has some additional features:.
 *
 * - Parameters passed to execute() are quoted according to their PHP type.
 * - A PHP NULL value will result in an actual SQL NULL value in the query.
 * - Array types are supported for all placeholders ("WHERE value IN (?)").
 * - Positional and named parameters can be mixed in the same query.
 */
class StudipPDOStatement implements IteratorAggregate
{
    protected $db;
    protected $query;
    protected $options;
    protected $columns;
    protected $params;
    protected $count;
    protected $stmt;

    /**
     * Initializes a new StudipPDOStatement instance.
     */
    public function __construct($db, $query, $options)
    {
        $this->db = $db;
        $this->query = $query;
        $this->options = $options;
        $this->params = array();
    }

    /**
     * Injects a PDOStatement.
     */
    public function setStatement(PDOStatement $statement)
    {
        $this->stmt = $statement;
    }

    /**
     * Arranges to have a particular variable bound to a given column in
     * the result-set from a query. Each call to fetch() or fetchAll()
     * will update all the variables that are bound to columns.
     */
    public function bindColumn($column, &$param/*, ...*/)
    {
        $args = func_get_args();
        $args[1] = &$param;
        $this->columns[] = $args;

        return true;
    }

    /**
     * Binds a PHP variable to a corresponding named or question mark place-
     * holder in the SQL statement that was used to prepare the statement.
     * Unlike bindValue(), the variable is bound as a reference and will
     * only be evaluated at the time that execute() is called.
     */
    public function bindParam($parameter, &$variable, $data_type = null)
    {
        if (is_string($parameter) && $parameter[0] !== ':') {
            $parameter = ':'.$parameter;
        }

        $this->params[$parameter] = array('value' => &$variable, 'type' => $data_type);

        return true;
    }

    /**
     * Binds a value to a corresponding named or question mark placeholder
     * in the SQL statement that was used to prepare the statement.
     */
    public function bindValue($parameter, $value, $data_type = null)
    {
        if (is_string($parameter) && $parameter[0] !== ':') {
            $parameter = ':'.$parameter;
        }

        $this->params[$parameter] = array('value' => $value, 'type' => $data_type);

        return true;
    }

    /**
     * Forwards all unknown methods to the actual statement object.
     */
    public function __call($name, array $arguments)
    {
        $callable = array($this->stmt, $name);
        if (!is_callable($callable)) {
            throw new BadMethodCallException();
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Forwards all Iterator methods to the actual statement object.
     */
    public function getIterator()
    {
        return $this->stmt;
    }

    /**
     * Executes the prepared statement and returns a PDOStatement object.
     */
    public function execute($input_parameters = null)
    {
        // bind additional parameters from execute()
        if (isset($input_parameters)) {
            foreach ($input_parameters as $key => $value) {
                $this->bindValue(is_int($key) ? $key + 1 : $key, $value, null);
            }
        }

        // emulate prepared statement if necessary
        foreach ($this->params as $key => $param) {
            if ($param['type'] === StudipPDO::PARAM_ARRAY ||
                $param['type'] === StudipPDO::PARAM_COLUMN ||
                $param['type'] === null && !is_string($param['value'])) {
                $emulate_prepare = true;
                break;
            }
        }

        // build the actual query string and prepared statement
        if ($emulate_prepare) {
            $this->count = 1;
            $query = preg_replace_callback('/\?|:\w+/', array($this, 'replaceParam'), $this->query);
        } else {
            $query = $this->query;
        }

        $this->stmt = $this->db->prepareStatement($query, $this->options);

        // bind query parameters on the actual statement
        if (!$emulate_prepare) {
            foreach ($this->params as $key => $param) {
                $this->stmt->bindValue($key, $param['value'], $param['type']);
            }
        }

        // set up column bindings on the actual statement
        if (isset($this->columns)) {
            foreach ($this->columns as $args) {
                call_user_func_array(array($this->stmt, 'bindColumn'), $args);
            }
        }

        return $this->stmt->execute();
    }

    /**
     * Replaces a placeholder with the corresponding parameter value.
     * Throws an exception if there is no corresponding value.
     */
    protected function replaceParam($matches)
    {
        $name = $matches[0];

        if ($name == '?') {
            $key = $this->count++;
        } else {
            $key = $name;
        }

        if (!isset($this->params[$key])) {
            throw new PDOException('missing parameter in query: '.$key);
        }

        return $this->db->quote($this->params[$key]['value'], $this->params[$key]['type']);
    }

    /**
     * Returns the result set rows as a grouped associative array. The first field
     * of each row is used as the array's keys.
     * optionally apply given callable on each grouped row to aggregate results
     * if no callable is given, 'current' is used, to return the first entry of the grouped row.
     *
     * @param int      $fetch_style Either PDO::FETCH_ASSOC or PDO::FETCH_COLUMN
     * @param callable $group_func  function to aggregate grouped rows
     *
     * @return array grouped result set
     */
    public function fetchGrouped($fetch_style = PDO::FETCH_ASSOC, $group_func = 'current')
    {
        if (!($fetch_style & (PDO::FETCH_ASSOC | PDO::FETCH_COLUMN))) {
            throw new PDOException('Fetch style not supported, try FETCH_ASSOC or FETCH_COLUMN');
        }

        $fetch_style |= PDO::FETCH_GROUP;
        $rows = $this->fetchAll($fetch_style);

        return is_callable($group_func) ? array_map($group_func, $rows) : $rows;
    }

    /**
     * Returns the result set rows as a grouped associative array. The first field
     * of each row is used as the array's keys, the other one is grouped
     * use only when selecting 2 columns
     * optionally apply given callable on each grouped row to aggregate results.
     *
     * @param callable $group_func function to aggregate grouped rows
     *
     * @return array grouped result set
     */
    public function fetchGroupedPairs($group_func = null)
    {
        return $this->fetchGrouped(PDO::FETCH_COLUMN, $group_func);
    }

    /**
     * Returns result rows as associative array, first colum as key,
     * second as value. Use only when selecting 2 columns.
     *
     * @return array result set
     */
    public function fetchPairs()
    {
        return $this->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns sequential array with values from first colum.
     *
     * @return array first row result set
     */
    public function fetchFirst()
    {
        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Returns only first row of result set as associative array.
     *
     * @return array first row result set
     */
    public function fetchOne()
    {
        $data = $this->fetch(PDO::FETCH_ASSOC);

        return $data ?: array();
    }
}
