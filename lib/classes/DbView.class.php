<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DbView.class.php
// Class to provide simple Views and Prepared Statements
// Mainly for MySql, may work with other DBs (not tested)
// Copyright (c) 2002 André Noack <andre.noack@gmx.net>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


/**
 * Class to provide simple Views and Prepared Statements
 *
 * Only tested with MySql, needs MySql >= 3.23
 * Uses DB abstraction layer of PHPLib
 *
 * @access   public
 * @author   André Noack <andre.noack@gmx.net>
 * @package  DBTools
 */
class DbView
{
    /**
     * the processed list of queries
     *
     *
     * @access   private
     * @var      array $query_list
     */
    private $query_list = [];
    /**
     * list of parameters
     *
     *
     * @access   public
     * @var      array $params
     */
    public $params = [];

    /**
     * Database Object
     *
     *
     * @access   private
     * @var      object $db
     */
    private $db;
    /**
     * Database Object Type
     *
     * Use your subclass of db_mysql here, or pass existing object to constuctor
     *
     * @access   private
     * @var      string $db_class_name
     * @see      DbView()
     */
    private $db_class_name = "DB_Seminar";
    /**
     * Temp Table Type
     *
     * MyISAM is always safe, HEAP may provide better performance
     *
     * @access   private
     * @var      string $temp_table_type
     */
    private $temp_table_type = "MyISAM";
    /**
     * Primary Key used in Temp Table
     *
     * If none is set in your view, an auto_increment row is used
     *
     * @access   private
     * @var      string $pk
     * @see      get_temp_table()
     */
    private $pk = "";
    /**
     * delete the params array after each query execution
     *
     *
     * @access   public
     * @var      boolean $auto_free_params
     */
    public $auto_free_params = true;
    /**
     * turn on/off debugging
     *
     *
     * @access   public
     * @var      boolean $debug
     */
    public $debug = false;

    static protected $dbviewfiles = [];

    static protected $dbviews = [];

    public static function addView($view)
    {
        $view = mb_strtolower($view);
        if (!isset(self::$dbviewfiles[$view])) {
            self::$dbviewfiles[$view] = 0;
        }
    }

    /**
     * Convenience method that combines addView() and returns an instance.
     *
     * @param String $view Required view (at least this will be present in the
     *                     returned instance)
     * @param mixed  $db classname of db abstraction or existing db object
     *
     * @return DbView Instance of self with at least the required view loaded
     */
    public static function getView($view, $db = '')
    {
        self::addView($view);

        return new self($db);
    }

    /**
     * Constructor
     *
     * Pass nothing to use a new instance of db_class_name, the classname for a new instance, or existing instance
     *
     * @access   public
     *
     * @param    mixed $db classname of used db abstraction or existing db object
     */
    public function __construct($db = "")
    {
        if (is_object($db)) {
            $this->db = $db;
        } else if ($db != "") {
            $this->db            = new $db;
            $this->db_class_name = $db;
        } else {
            $this->db = new $this->db_class_name;
        }
        $this->init_views();
    }

    public function init_views()
    {
        foreach (self::$dbviewfiles as $view => $status) {
            if ($status === 0) {
                include 'lib/dbviews/' . $view . '.view.php';
                self::$dbviews += $_views;
                unset($_views);
                self::$dbviewfiles[$view] = 1;
            }
        }
    }

    public function __get($view)
    {
        if (isset(self::$dbviews[$view])) {
            return self::$dbviews[$view];
        } else {
            return null;
        }
    }

    /**
     * print error message and exit script
     *
     * @access   private
     *
     * @param    string $msg the message to print
     */
    public function halt($msg)
    {
        echo "<hr>$msg<hr>";
        if ($this->debug) {
            echo "<pre>";
            print_r($this);
            echo "</pre>";
        }
        die;
    }

    public function get_query()
    {
        $parsed_query = $this->get_parsed_query(func_get_args());
        $this->db->cache_query($parsed_query);
        return $this->db;
    }

    public function get_parsed_query($query_list)
    {
        $parsed_query     = "";
        $this->query_list = [];
        (is_array($query_list)) ? $this->query_list = $query_list : $this->query_list[] = $query_list;
        if (count($this->query_list) == 1) {
            $spl = explode(":", $this->query_list[0]);
            if ($spl[0] == "view") {
                $this->query_list = $this->get_view(trim($spl[1]));
            }
        }
        $this->parse_query($this->query_list);
        if (is_array($this->query_list)) {
            $parsed_query = $this->query_list[0];
        } else {
            $parsed_query = $this->query_list;
        }

        return $parsed_query;
    }


    public function parse_query(&$query)
    {
        if (is_array($query)) {
            for ($i = (count($query) - 1); $i > 0; --$i) {
                $spl = explode(":", $query[$i]);
                if ($spl[0] == "view") {
                    $query[$i] = $this->get_view(trim($spl[1]), $spl[2]);
                }
                $query[$i]  = $this->parse_query($query[$i]);
                $repl_query = (is_array($query[$i])) ? $query[$i][0] : $query[$i];
                for ($j = 0; $j < $i; ++$j) {
                    $spl = mb_stristr($query[$j], "where");
                    if (!$spl)
                        $spl = mb_stristr($query[$j], "having");
                    if ($spl) {
                        $pos = mb_strpos($spl, "{" . $i . "}");
                        if (!$pos === false)
                            $repl_query = $this->get_temp_values($repl_query);
                    }
                    if (!$spl OR $pos === false) {
                        $pos = mb_strpos($query[$j], "{" . $i . "}");
                        if (!$pos === false)
                            $repl_query = $this->get_temp_table($repl_query);
                    }
                    $query[$j] = str_replace("{" . $i . "}", $repl_query, $query[$j]);
                }
            }
        }

        return $query;
    }


    public function get_temp_table($sub_query)
    {
        $id    = self::get_uniqid();
        $pk    = $this->pk ? "PRIMARY KEY($this->pk)" : "auto_" . $id . " INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
        $query = "CREATE TEMPORARY TABLE temp_$id ($pk) ENGINE=$this->temp_table_type $sub_query";
        $this->db->query($query);

        return " temp_" . $id . " ";
    }


    public function get_temp_values($sub_query)
    {
        $this->db->query($sub_query);
        if (!$this->db->num_rows())
            $this->halt("Sub Query: <b>$sub_query</b> returns nothing!");
        else {
            while ($this->db->next_record()) {
                $result[] = $this->db->Record[0];
            }
            $value_list = $this->get_value_list($result);
        }

        return $value_list;
    }

    public static function get_uniqid()
    {
        mt_srand((double)microtime() * 1000000);

        return md5(uniqid(mt_rand(), 1));
    }

    public function get_value_list($list)
    {
        $value_list = false;
        if (count($list) == 1)
            $value_list = "'$list[0]'";
        else
            $value_list = "'" . join("','", $list) . "'";

        return $value_list;
    }

    public function get_view($name)
    {
        if (self::$dbviews[$name]["pk"])
            $this->pk = self::$dbviews[$name]["pk"];
        if (self::$dbviews[$name]["temp_table_type"])
            $this->temp_table_type = self::$dbviews[$name]["temp_table_type"];
        if (!$query_list = self::$dbviews[$name]["query"])
            $this->halt("View not found: $name");
        (is_array($query_list)) ? $query = $query_list[0] : $query = $query_list;
        $tokens = preg_split("/[\?§\&]/u", $query);
        if (count($tokens) > 1) {
            $types = [];
            $token = 0;
            foreach (preg_split('//u', $query, null, PREG_SPLIT_NO_EMPTY) as $i => $c) {
                switch ($c) {
                    case '?':
                        $types[$token++] = 1;
                        break;
                    case '§':
                        $types[$token++] = 2;
                        break;
                    case '&':
                        $types[$token++] = 3;
                        break;
                }
            }
            if (count($this->params) != count($types))
                $this->halt("Wrong parameter count in view: $name");
            $query = "";
            for ($i = 0; $i < count($this->params); ++$i) {
                $query .= $tokens[$i];
                if (is_null($this->params[$i])) {
                    $query .= 'NULL';
                } else {
                    switch ($types[$i]) {
                        case 1:
                            $query .= "'" . $this->params[$i] . "'";
                            break;
                        case 2:
                            $query .= $this->params[$i];
                            break;
                        case 3:
                            $query .= (is_array($this->params[$i])) ? "'" . join("','", $this->params[$i]) . "'" : "'" . $this->params[$i] . "'";
                            break;
                    }
                }
            }
            $query .= $tokens[$i];
            if ($this->auto_free_params)
                $this->params = [];
        }
        (is_array($query_list)) ? $query_list[0] = $query : $query_list = $query;
        return $query_list;
    }

    public function Get_union()
    {
        $queries     = func_get_args();
        $view        = new DbView();
        $union_table = $view->get_temp_table($view->get_parsed_query($queries[0]));
        if ($queries[1]) {
            for ($i = 1; $i < count($queries); ++$i) {
                $view->db->query("REPLACE INTO $union_table " . $view->get_parsed_query($queries[$i]));
            }
        }

        return $union_table;
    }
}

?>
