<?php

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class DB_Sql {

    /* public: connection parameters */
    public $Host     = "";
    public $Database = "";
    public $User     = "";
    public $Password = "";

    /* public: configuration parameters */
    public $Auto_Free     = 1;     ## Set to 1 for automatic mysql_free_result()
    public $Debug         = 0;     ## Set to 1 for debugging messages.
    public $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)

    /* public: result array and current row number */
    public $Record   = [];
    public $Row;
    public $RowCount;
    public $ColumnCount;

    /* public: current error number and error text */
    public $Errno    = 0;
    public $Error    = "";

    protected $pdo;
    protected $resultSet;

    /* public: constructor */
    function __construct($query = "") {
        $this->pdo = DBManager::get();
        $this->query($query);
    }

    function link_id() {
        return TRUE;
    }

    function query_id() {
        return !is_null($this->resultSet);
    }

    /* public: discard the query result */
    function free() {
        $this->resultSet = NULL;
        $this->Errno     = NULL;
        $this->Error     = NULL;
        $this->Row       = 0;
        $this->RowCount  = null;
        $this->ColumnCount  = null;
    }

    /* public: perform a query */
    function query($Query_String) {

        $Query_String = trim($Query_String);
        if ($Query_String == "") {
            return 0;
        }

        # New query, discard previous result.
        $this->free();

        if ($this->Debug) {
            printf("Debug: query = %s<br>\n", $Query_String);
        }
        if(mb_stripos($Query_String, 'select') === 0 || mb_stripos($Query_String, 'show') === 0){

            $this->resultSet = $this->pdo->query($Query_String);

            # Will return nada if it fails. That's fine.
            return $this->resultSet;
        } else {
            $this->RowCount = $this->pdo->exec($Query_String);
            return $this->RowCount;
        }
    }

    /* public: walk result set */
    function next_record() {
        if (!$this->resultSet) {
            //$this->halt("next_record called with no query pending.");
            return 0;
        }

        $this->Record = $this->resultSet->fetch();
        $this->Row   += 1;
        /*
        $this->Errno  = $this->resultSet->errorCode();
        $this->Error  = $this->resultSet->errorInfo();
        */
        $stat = is_array($this->Record);
        if ($this->Row == $this->num_rows()) {
            $this->ColumnCount = $this->resultSet->ColumnCount();
            $this->resultSet = null;
        }
        return $stat;
    }

    /* public: evaluate the result (size, width) */
    function affected_rows() {
        return $this->num_rows();
    }

    function num_rows() {
        return (!is_null($this->RowCount) ? $this->RowCount : ($this->resultSet ? ($this->RowCount = $this->resultSet->rowCount()) : FALSE));
    }

    function num_fields() {
        return (!is_null($this->ColumnCount) ? $this->ColumnCount : ($this->resultSet ? ($this->ColumnCount = $this->resultSet->ColumnCount()) : FALSE));
    }

    /* public: shorthand notation */
    function nf() {
        return $this->num_rows();
    }

    function np() {
        print $this->num_rows();
    }

    function f($Name) {
        return $this->Record[$Name];
    }

    function p($Name) {
        print $this->Record[$Name];
    }

    /* public: return table metadata */
    function metadata($table='',$full=false) {
        $count = 0;
        $id    = 0;
        $res   = [];

        /*
        * Due to compatibility problems with Table we changed the behavior
        * of metadata();
        * depending on $full, metadata returns the following values:
        *
        * - full is false (default):
        * $result[]:
        *   [0]["table"]  table name
        *   [0]["name"]   field name
        *   [0]["type"]   field type
        *   [0]["len"]    field length
        *   [0]["flags"]  field flags
        *
        * - full is true
        * $result[]:
        *   ["num_fields"] number of metadata records
        *   [0]["table"]  table name
        *   [0]["name"]   field name
        *   [0]["type"]   field type
        *   [0]["len"]    field length
        *   [0]["flags"]  field flags
        *   ["meta"][field name]  index of field named "field name"
        *   The last one is used, if you have a field name, but no index.
        *   Test:  if (isset($result['meta']['myfield'])) { ...
            */

            // if no $table specified, assume that we are working with a query
            // result
            if ($table) {
                throw new Exception('Not yet implemented.');
                $this->connect();
                $id = @mysql_list_fields($this->Database, $table);
                if (!$id)
                $this->halt("Metadata query failed.");
            } else {
                if (is_null($this->resultSet))
                $this->halt("No query specified.");
            }

            $count = $this->resultSet->ColumnCount();

            // made this IF due to performance (one if is faster than $count if's)
            if (!$full) {
                for ($i = 0; $i < $count; $i++) {
                    $meta = $this->resultSet->getColumnMeta($i);
                    $res[$i]["table"] = $meta['table'];
                    $res[$i]["name"]  = $meta['name'];
                    $res[$i]["type"]  = $meta['native_type'];
                    $res[$i]["len"]   = $meta['len'];
                    $res[$i]["flags"] = $meta['flags'];
                }
            } else { // full
                throw new Exception('Not yet implemented.');
                $res["num_fields"]= $count;

                for ($i=0; $i<$count; $i++) {
                    $res[$i]["table"] = @mysql_field_table ($id, $i);
                    $res[$i]["name"]  = @mysql_field_name  ($id, $i);
                    $res[$i]["type"]  = @mysql_field_type  ($id, $i);
                    $res[$i]["len"]   = @mysql_field_len   ($id, $i);
                    $res[$i]["flags"] = @mysql_field_flags ($id, $i);
                    $res["meta"][$res[$i]["name"]] = $i;
                }
            }

            // free the result only if we were called on a table
            if ($table) {
                throw new Exception('Not yet implemented.');
                @mysql_free_result($id);
            }

            return $res;
    }

    /* private: error handling */
    function halt($msg) {
        if ($this->Halt_On_Error == "no")
        return;

        $this->haltmsg($msg);

        if ($this->Halt_On_Error != "report")
        die("Session halted.");
    }

    function haltmsg($msg) {
        printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
        printf("<b>MySQL Error</b>: %s (%s)<br>\n",
        $this->Errno,
        join(':',$this->Error));
    }

    /* public: perform a query using format string*/
    function queryf($format /* , .. */) {

        // get args
        $args = func_get_args();

        // get format string
        $format = array_shift($args);

        // do something
        return $this->query(vsprintf($format, $args));
    }

    /* public: perform a query with caching directive for mysql*/
    function cache_query($Query_String) {
        return $this->query(preg_replace("/^select\b/i", "SELECT SQL_CACHE", $Query_String));
    }
}
?>
