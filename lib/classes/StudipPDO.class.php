<?php
/**
 * StudipPDO.class.php - Stud.IP PDO class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This is a special variant of the standard PDO class that does
 * not allow multiple statement execution.
 */
class StudipPDO extends PDO
{
    const PARAM_ARRAY  = 100;
    const PARAM_COLUMN = 101;

    // Counter for the queries sent to the database
    public $query_count = 0;
    public $queries     = [];

    /**
     * Verifies that the given SQL query only contains a single statement.
     *
     * @param string    SQL statement to check
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

        if ($GLOBALS['DEBUG_ALL_DB_QUERIES']) {
            $trace = debug_backtrace();

            $classes = [];
            if (isset($trace[2]['class']) && $trace[2]['class'] === 'SimpleORMap') {
                $classes[] = 'sorm';
            }
            if (isset($trace[1]) && $trace[1]['function'] === 'prepare') {
                $classes[] = 'prepared';
            }

            $this->queries[] = [
                'query'   => implode("\n", array_filter(array_map('trim', explode("\n", $statement)))),
                'classes' => implode(' ', $classes),
                'trace'   => $GLOBALS['DEBUG_ALL_DB_QUERIES_WITH_TRACE']
                           ? array_slice($trace, 2)
                           : null,
            ];
        }
    }

    /**
     * Replaces all string literals in the statement with placeholders.
     *
     * @param string    SQL statement
     * @return string   modified SQL statement
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
                if ($quote_chr !== NULL) {
                    if ($part === $quote_chr) {
                        $part = next($parts);

                        if ($part !== $quote_chr) {
                            // backtrack and terminate string
                            prev($parts);
                            $result .= '?';
                            $quote_chr = NULL;
                        }
                    } else if ($part === '\\') {
                        // skip next part
                        next($parts);
                    }
                } else if ($part === "'" || $part === '"') {
                    $quote_chr = $part;
                    $saved_pos = key($parts);
                } else {
                    $result .= $part;
                }
            }

            if ($quote_chr !== NULL) {
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
     * @return string   quoted SQL string
     */
    public function quote($value, $type = NULL)
    {
        if (!isset($type)) {
            if (is_null($value)) {
                $type = PDO::PARAM_NULL;
            } else if (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } else if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } else if (is_array($value)) {
                $type = StudipPDO::PARAM_ARRAY;
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
            case StudipPDO::PARAM_ARRAY:
                return is_array($value) && count($value) ? join(',', array_map(array($this, 'quote'), $value)) : 'NULL';
            case StudipPDO::PARAM_COLUMN:
                return preg_replace('/\\W/', '', $value);
            default:
                return parent::quote($value);
        }
    }

    /**
     * Executes an SQL statement and returns the number of affected rows.
     *
     * @param string    SQL statement
     * @return int      number of affected rows
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
     * @return object   PDOStatement object
     */
    public function query($statement, $mode = NULL, $arg1 = NULL, $arg2 = NULL)
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
     * @return object   PDOStatement object
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
     * @return object   PDOStatement object
     */
    public function prepareStatement($statement, $driver_options = array())
    {
        return parent::prepare($statement, $driver_options);
    }

    /**
     * Executes sql statement with given parameters,
     * returns number of affected rows, use only for INSERT,UPDATE etc
     *
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @return integer number of affected rows
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
     * optionally apply given callable on each row, with current row and key as parameter
     *
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param callable $callable callable to be applied to each of the rows
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
     * optionally apply given callable on each row, with current value and key as parameter
     *
     * @see StudipPDOStatement::fetchFirst()
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param callable $callable callable to be applied to each of the rows
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
     * if no callable is given, 'current' is used, to return the first entry of the grouped row
     *
     * @see StudipPDOStatement::fetchGrouped()
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param callable $callable callable to be applied to each of the rows
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
     * optionally apply given callable on each grouped row, with current row and key as parameter
     *
     * @see StudipPDOStatement::fetchGroupedPairs()
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param callable $callable callable to be applied to each of the rows
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
     * optionally apply given callable on each grouped row, with current row and key as parameter
     *
     * @see StudipPDOStatement::fetchGroupedPairs()
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param callable $callable callable to be applied to each of the rows
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
     * as associative array
     *
     * @see StudipPDOStatement::fetchOne()
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
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
     * third param denotes the column, zero indexed
     *
     * @param string $statement SQL statement to execute
     * @param array $input_parameters parameters for statement
     * @param integer $column number of column to fetch
     * @return string value of chosen column
     */
    public function fetchColumn($statement, $input_parameters = null, $column = 0)
    {
        $st = $this->prepare($statement);
        $st->execute($input_parameters);
        return $st->fetchColumn($column);
    }
}
