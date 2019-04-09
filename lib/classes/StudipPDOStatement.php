<?php
/**
 * This is a "fake" PDOStatement implementation that behaves mostly like
 * a real statement object, but has some additional features:
 *
 * - Parameters passed to execute() are quoted according to their PHP type.
 * - A PHP NULL value will result in an actual SQL NULL value in the query.
 * - Array types are supported for all placeholders ("WHERE value IN (?)").
 * - Positional and named parameters can be mixed in the same query.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
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
        $this->params = [];
    }

    /**
     * Injects a PDOStatement
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
            $parameter = ':' . $parameter;
        }

        $this->params[$parameter] = ['value' => &$variable, 'type' => $data_type];
        return true;
    }

    /**
     * Binds a value to a corresponding named or question mark placeholder
     * in the SQL statement that was used to prepare the statement.
     */
    public function bindValue($parameter, $value, $data_type = null)
    {
        if (is_string($parameter) && $parameter[0] !== ':') {
            $parameter = ':' . $parameter;
        }

        $this->params[$parameter] = ['value' => $value, 'type' => $data_type];
        return true;
    }

    /**
     * Forwards all unknown methods to the actual statement object.
     */
    public function __call($name, array $arguments)
    {
        $callable = [$this->stmt, $name];
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
    public function execute($input_parameters = NULL)
    {
        // bind additional parameters from execute()
        if (isset($input_parameters)) {
            foreach ($input_parameters as $key => $value) {
                $this->bindValue(is_int($key) ? $key + 1 : $key, $value, NULL);
            }
        }

        // emulate prepared statement if necessary
        foreach ($this->params as $key => $param) {
            if ($param['type'] === StudipPDO::PARAM_ARRAY ||
                $param['type'] === StudipPDO::PARAM_COLUMN ||
                $param['type'] === NULL && !is_string($param['value'])) {
                $emulate_prepare = true;
                break;
            }
        }

        // build the actual query string and prepared statement
        if ($emulate_prepare) {
            $this->count = 1;
            $query = preg_replace_callback('/\?|:\w+/', [$this, 'replaceParam'], $this->query);
        } else {
            $query = $this->query;
        }

        $this->stmt = $this->db->prepareStatement($query, $this->options);

        // bind query parameters on the actual statement
        if (!$emulate_prepare) {
            foreach ($this->params as $key => $param) {
                $this->stmt->bindValue($key, $param['value'], $param['type'] ?: PDO::PARAM_STR);
            }
        }

        // set up column bindings on the actual statement
        if (isset($this->columns)) {
            foreach ($this->columns as $args) {
                call_user_func_array([$this->stmt, 'bindColumn'], $args);
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
            throw new PDOException('missing parameter in query: ' . $key);
        }

        return $this->db->quote($this->params[$key]['value'], $this->params[$key]['type']);
    }

    /**
     * Returns the result set rows as a grouped associative array. The first field
     * of each row is used as the array's keys.
     * optionally apply given callable on each grouped row to aggregate results
     * if no callable is given, 'current' is used, to return the first entry of the grouped row
     *
     * @param int   $fetch_style    Either PDO::FETCH_ASSOC or PDO::FETCH_COLUMN
     * @param callable $group_func  function to aggregate grouped rows
     * @return array grouped result set
     */
    public function fetchGrouped($fetch_style = PDO::FETCH_ASSOC, $group_func = 'current') {
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
     * optionally apply given callable on each grouped row to aggregate results
     *
     * @param callable $group_func  function to aggregate grouped rows
     * @return array grouped result set
     */
    public function fetchGroupedPairs($group_func = null)
    {
        return $this->fetchGrouped(PDO::FETCH_COLUMN, $group_func);
    }

    /**
     * Returns result rows as associative array, first colum as key,
     * second as value. Use only when selecting 2 columns
     *
     * @return array result set
     */
    public function fetchPairs()
    {
        return $this->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns sequential array with values from first colum
     *
     * @return array first row result set
     */
    public function fetchFirst()
    {
        return $this->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Returns only first row of result set as associative array
     *
     * @return array first row result set
     */
    public function fetchOne()
    {
        $data = $this->fetch(PDO::FETCH_ASSOC);
        return $data ?: [];
    }
}
