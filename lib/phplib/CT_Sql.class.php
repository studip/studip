<?php

##
## Copyright (c) 1998-2000 NetUSE AG
##                    Boris Erdmann, Kristian Koehntopp
##
## Copyright (c) 1998-2000 Sascha Schumann <sascha@schumann.cx>
##
## PHPLIB Data Storage Container using a SQL database
## for use with Stud.IP and PDO only!

class CT_Sql {
    
    ##
    ## Define these parameters by overwriting or by
    ## deriving your own class from it (recommened)
    ##
    
    var $database_table = "session_data";
    var $gzip_level = 0;
    var $exists = '';
    
    ## end of configuration
    
    function ac_start() {
    }
    
    function ac_get_lock() {
        return true;
    }
    
    function ac_release_lock() {
        return true;
    }
    
    function ac_gc($gc_time, $name = null) {
        return DBManager::get()->exec(sprintf("DELETE FROM %s WHERE changed < FROM_UNIXTIME(%s) ",
            $this->database_table,
            (time() - ($gc_time * 60))
            ));
    }
    
    function ac_store($id, $name, $str) {
        $db = DBManager::get();
        if ($this->gzip_level){
            $str = gzcompress($str, $this->gzip_level);
        }
        if ($this->exists === $id) {
            $stmt = $db->prepare(sprintf("UPDATE %s SET val = ? WHERE sid = ?", $this->database_table));
        } else {
            $stmt = $db->prepare(sprintf("REPLACE INTO %s ( val, sid ) VALUES (?, ?)", $this->database_table));
        }
        $stmt->execute(array($str, $id));
        return $stmt->rowCount();
    }
    
    function ac_delete($id, $name = null) {
        return DBManager::get()->exec(sprintf("DELETE FROM %s WHERE sid = '%s' LIMIT 1",
            $this->database_table,
            $id));
    }
    
    function ac_get_value($id, $name = null) {
        $rs = DBManager::get()->query(sprintf("SELECT val FROM %s where sid  = '%s'",
            $this->database_table,
            $id));
        $str  = $rs->fetchColumn();
        if ($this->gzip_level){
            $str = @gzuncompress($str);
        }
        if ($str) $this->exists = $id;
        return $str;
    }
    
    function ac_get_changed($id, $name = null){
        $rs = DBManager::get()->query(sprintf("SELECT UNIX_TIMESTAMP(changed) FROM %s WHERE sid  = '%s'",
            $this->database_table,
            $id));
        return $rs->fetchColumn();
    }
    
    function ac_set_changed($id, $name = null, $timestamp){
        $db = DBManager::get();
        $stmt = $db->prepare(sprintf("UPDATE %s SET changed = FROM_UNIXTIME(?) WHERE sid  = ?", $this->database_table));
        $stmt->execute(array($timestamp, $id));
        return $stmt->rowCount();
    }
    
    function ac_newid($str, $name = null) {
        $db = DBManager::get();
        $query = "SELECT sid FROM " . $this->database_table . " WHERE sid = '$str'";
        if (!$db->query($query)->fetchColumn()) {
            return $str;
        } else {
            return FALSE;
        }
    }
    
    function ac_halt($s) {
    }
}
?>
