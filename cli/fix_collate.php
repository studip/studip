#!/usr/bin/env php
<?php
/**
 * @author Witali Mik <mik@data-quest.de>
 * Script um Collation Konflikte automatisiert zu lösen
 */

require_once dirname(__FILE__) . '/studip_cli_env.inc.php';
require_once 'lib/classes/DBManager.class.php';
require_once 'config/config_local.inc.php';

$charset = 'latin1';
$collate = 'latin1_german1_ci';
$sql = "SELECT CONCAT('ALTER TABLE `".$DB_STUDIP_DATABASE."`.`', TABLE_NAME, '` CONVERT TO CHARACTER SET ".$charset." COLLATE ".$collate.";') as query  FROM `information_schema`.TABLES WHERE TABLE_SCHEMA='".$DB_STUDIP_DATABASE."' AND TABLE_COLLATION!='".$collate."'";

$db = DBManager::get();


$result = $db->query($sql);
foreach($result->fetchAll(PDO::FETCH_OBJ) as $row){
   $db->exec($row->query);
    fwrite(STDOUT, sprintf("Execute: %s \n",$row->query));
}
fwrite(STDOUT, "Finished");