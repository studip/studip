<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DbSnapshot.class.php
// Class to provide snapshots of mysql result sets
// Uses PHPLib DB Abstraction
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
 * Class to provide snapshots of mysql result sets
 *
 * Uses DB abstraction layer of PHPLib
 *
 * @access   public
 * @author   André Noack <andre.noack@gmx.net>
 * @package  DBTools
 **/
class DbSnapshot
{

    /**
     * the used db abstraction class
     *
     *
     * @access   private
     * @var      string $DbClass
     */
    var $DbClass = "DB_Sql";
    /**
     * the used db result set
     *
     *
     * @access   private
     * @var      object DB_Sql   $dbResult
     */
    var $dbResult = null;
    /**
     * array to store the result set
     *
     *
     * @access   private
     * @var      array $result
     */
    var $result = [];
    /**
     * array to store metadata oh the result set
     *
     *
     * @access   private
     * @var      array $metaData
     */
    var $metaData = [];
    /**
     * the number of fields in the result set
     *
     *
     * @access   public
     * @var      integer $numFields
     */
    var $numFields = 0;
    /**
     * the number of rows in the result set
     *
     *
     * @access   public
     * @var      integer $numRows
     */
    var $numRows = 0;
    /**
     * the internal row pointer
     *
     *
     * @access   private
     * @var      mixed $pos
     */
    var $pos = false;
    /**
     * turn on/off debugging
     *
     *
     * @access   public
     * @var      boolean $debug
     */
    var $debug = false;

    /**
     * Constructor
     *
     * Pass instance of DbClass or nothing to specify result set later
     *
     * @access   public
     *
     * @param    object DB_Sql   $dbresult
     */
    public function __construct($dbresult = null)
    {
        if (is_object($dbresult)) {
            $this->dbResult = $dbresult;
            $this->getSnapshot();
        }

    }

    function isDbResult()
    {
        if (!is_subclass_of($this->dbResult, $this->DbClass))
            $this->halt("Result set has wrong type!");
        if (!$this->dbResult->query_id())
            $this->halt("No result set (missing query?)");

        return true;
    }

    public function getSnapshot()
    {
        if ($this->isDbResult()) {
            $this->numFields = $this->dbResult->num_fields();
            $this->numRows   = $this->dbResult->num_rows();
            $this->metaData  = $this->dbResult->metadata();
            $this->result    = [];
            while ($this->dbResult->next_record()) {
                $this->result[] = $this->dbResult->Record;
            }
            unset($this->dbResult);
            $this->pos = false;

            return true;
        }

        return false;
    }

    public function nextRow()
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        if ($this->pos === false) {
            $this->pos = 0;

            return true;
        }
        if (++$this->pos < $this->numRows)
            return true;
        else
            return false;
    }

    public function resetPos()
    {
        $this->pos = false;
    }

    public function isField($name)
    {
        for ($i = 0; $i < $this->numFields; ++$i) {
            if ($name == $this->metaData[$i]['name']) {
                return true;
            }
        }

        return false;
    }

    public function getRow($row = false)
    {
        if (!$row === false AND !$this->result[$row])
            $this->halt("Snapshot has only " . ($this->numRows - 1) . " rows!");

        return ($row === false) ? $this->result[$this->pos] : $this->result[$row];
    }

    public function getFieldList()
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        $ret = [];
        for ($i = 0; $i < $this->numFields; ++$i) {
            $ret[] = $this->metaData[$i]['name'];
        }

        return $ret;
    }

    public function getField($field = 0)
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");

        return ($this->pos === false) ? false : $this->result[$this->pos][$field];
    }

    public function getRows($fieldname = 0)
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        $ret = [];
        for ($i = 0; $i < $this->numRows; ++$i) {
            $ret[] = $this->result[$i][$fieldname];
        }

        return $ret;
    }

    public function getDistinctRows($fieldname)
    {
        if (!$this->isField($fieldname))
            $this->halt("Field: $fieldname not found in result set!");
        $ret = [];
        for ($i = 0; $i < $this->numRows; ++$i) {
            $ret[$this->result[$i][$fieldname]]        = $this->result[$i];
            $ret[$this->result[$i][$fieldname]]['row'] = $i;
        }

        return $ret;
    }

    public function sortRows($fieldname = 0, $order = "ASC", $stype = false)
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        $sortfields = $this->getRows($fieldname);
        if ($stype !== false) {
            $sortfunc = ($order == "ASC") ? "asort" : "arsort";
            $sortfunc($sortfields, $stype);
        } else {
            uasort($sortfields, function ($a,$b) {
                $a = mb_strtolower($a);
                $a = str_replace('ä', 'ae', $a);
                $a = str_replace('ö', 'oe', $a);
                $a = str_replace('ü', 'ue', $a);

                $b = mb_strtolower($b);
                $b = str_replace('ä', 'ae', $b);
                $b = str_replace('ö', 'oe', $b);
                $b = str_replace('ü', 'ue', $b);

                return strnatcasecmp($a, $b);
            });
            if ($order == "DESC") {
                $sortfields = array_reverse($sortfields, true);
            }
        }
        $sortresult = [];
        foreach ($sortfields as $key => $value) {
            $sortresult[] = $this->result[$key];
        }
        $this->result = $sortresult;
        $this->resetPos();

        return true;
    }

    public function searchFields($fieldname, $searchstr)
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        $ret        = false;
        $sortfields = $this->getRows($fieldname);
        foreach ($sortfields as $key => $value) {
            if (preg_match($searchstr, $value)) {
                $ret       = true;
                $this->pos = $key;
                break;
            }
        }

        return $ret;
    }

    public function getGroupedResult($group_by_field, $fields_to_group = null)
    {
        if (!$this->numRows)
            $this->halt("No snapshot available or empty result!");
        $fieldlist = $this->getFieldList();
        if (!in_array($group_by_field, $fieldlist))
            $this->halt("group_by_field not found in result set!");
        if (is_array($fields_to_group))
            $fieldlist = $fields_to_group;
        $num_fields = count($fieldlist);
        $ret        = [];
        for ($i = 0; $i < $this->numRows; ++$i) {
            for ($j = 0; $j < $num_fields; ++$j) {
                if ($fieldlist[$j] != $group_by_field) {
                    ++$ret[$this->result[$i][$group_by_field]][$fieldlist[$j]][$this->result[$i][$fieldlist[$j]]];
                }
            }
        }

        return $ret;
    }

    public function mergeSnapshot($m_snap, $key_field = false, $mode = "ADD")
    {
        if ($mode == "ADD") {
            for ($i = 0; $i < $m_snap->numRows; ++$i) {
                $this->result[] = $m_snap->result[$i];
            }
        } elseif ($mode == "AND") {
            if (!$this->numRows || !$m_snap->numRows) {
                $this->result = [];
            } elseif ($m_snap->numRows) {
                $m_result = $m_snap->getDistinctRows($key_field);
                for ($i = 0; $i < $this->numRows; ++$i) {
                    if (!($m_result[$this->result[$i][$key_field]] && $this->result[$i][$key_field])) {
                        unset($this->result[$i]);
                    }
                }
            }
        } elseif ($mode == "OR") {
            if (!$this->numRows) {
                $this->result = $m_snap->result;
            } elseif ($m_snap->numRows) {
                $result = $this->getDistinctRows($key_field);
                for ($i = 0; $i < $m_snap->numRows; ++$i) {
                    if (!$result[$m_snap->result[$i][$key_field]]) {
                        $this->result[] = $m_snap->result[$i];
                    }
                }
            }
        }
        $this->result  = array_merge([], (array)$this->result);
        $this->numRows = count($this->result);
        $this->resetPos();

        return $this->numRows;
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
            die;
        }

    }
}

?>
