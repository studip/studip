<?php

/*
 * 02_step_102_datenfeldtypen.php - migration for StEP00102
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


class Step102Datenfeldtypen extends Migration {

  function description() {
    return 'modify db schema for StEP00102 to provide typed datafields';
  }

  function up() {
    DBManager::get()->exec("ALTER TABLE `datafields` CHANGE `object_type` `object_type` enum('sem','inst','user','userinstrole','usersemdata','roleinstdata') default NULL;");
    DBManager::get()->exec("ALTER TABLE `datafields` CHANGE `view_perms` `view_perms` enum('all','user','autor','tutor','dozent','admin','root') default NULL;");
    DBManager::get()->exec("ALTER TABLE `datafields` ADD `type` enum('bool','textline','textarea','selectbox','date','time','email','url','phone', 'radio', 'combo') NOT NULL default 'textline';");
    DBManager::get()->exec("ALTER TABLE `datafields` ADD `typeparam` text NOT NULL;");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` ADD `sec_range_id` varchar(32) NOT NULL default '';");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` DROP PRIMARY KEY , ADD PRIMARY KEY ( `datafield_id` , `range_id` , `sec_range_id` );");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` ADD INDEX `range_id` ( `range_id` , `datafield_id` );");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` ADD INDEX `datafield_id_2` (`datafield_id`,`sec_range_id`);");
    DBManager::get()->exec("ALTER TABLE `statusgruppe_user` ADD `visible` tinyint(4) NOT NULL default '1';");
    DBManager::get()->exec("ALTER TABLE `statusgruppe_user` ADD `inherit` tinyint(4) NOT NULL default '1';");
    DBManager::get()->exec("CREATE TABLE `aux_lock_rules` (`lock_id` varchar( 32 ) NOT NULL default '', `name` varchar( 255 ) NOT NULL default '', `description` text NOT NULL , `attributes` text NOT NULL , `sorting` text NOT NULL , PRIMARY KEY ( `lock_id` )) ENGINE=MyISAM;");
    DBManager::get()->exec("ALTER TABLE `seminare` ADD `aux_lock_rule` varchar(32) default NULL;");

    $this->migrate_datafields();
  }

  function down() {
    DBManager::get()->exec("ALTER TABLE `seminare` DROP `aux_lock_rule`;");
    DBManager::get()->exec("DROP TABLE `aux_lock_rules`;");
    DBManager::get()->exec("ALTER TABLE `statusgruppe_user` DROP `inherit`;");
    DBManager::get()->exec("ALTER TABLE `statusgruppe_user` DROP `visible`;");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` DROP INDEX `datafield_id_2`;");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` DROP INDEX `range_id`;");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` DROP PRIMARY KEY , ADD PRIMARY KEY ( `datafield_id` , `range_id` );");
    DBManager::get()->exec("ALTER TABLE `datafields_entries` DROP `sec_range_id`;");
    DBManager::get()->exec("ALTER TABLE `datafields` DROP `type`;");
    DBManager::get()->exec("ALTER TABLE `datafields` DROP `typeparam`;");
    DBManager::get()->exec("ALTER TABLE `datafields` CHANGE `view_perms` `view_perms` enum('all','user','autor','tutor','dozent','admin','root') NOT NULL default 'all';");
    DBManager::get()->exec("ALTER TABLE `datafields` CHANGE `object_type` `object_type` ENUM('sem','inst','user') default NULL;");
  }

  function migrate_datafields() {

    # only require, if exists
    if (!file_exists($GLOBALS['STUDIP_BASE_PATH']
                     . '/config/config_datafields.inc.php')) {
      return;
    }
    require_once 'config/config_datafields.inc.php';

    if (!isset($DATAFIELDS)) {
      return;
    }

    require_once 'lib/classes/DataFieldStructure.class.php';

    $ids = array_keys(DataFieldStructure::getDataFieldStructures());

    foreach ($DATAFIELDS as $id => $field) {

      if (!in_array($id, $ids)) {
        $this->write('Not existent: ' . $id);
        continue;
      }

      $struct = new DataFieldStructure(['datafield_id' => $id]);

      $mapping = ['text'     => 'textline',
                        'textarea' => 'textarea',
                        'checkbox' => 'bool',
                        'select'   => 'selectbox',
                        'combo'    => 'combo',
                        'radio'    => 'radio',
                        'date'     => 'date'];

      if (!isset($mapping[$field['type']])) {
        # TODO (mlunzena) what to do?
      }

      $type = $mapping[$field['type']];
      $type_param = '';

      if (in_array($type, ['selectbox', 'combo', 'radio'])) {
        $type_param = $this->get_type_param($field['options']);
      }

      $struct->setType($type);
      $struct->setTypeParam($type_param);
      $struct->store();
    }
  }

  function get_type_param($options) {
    $new_options = [];
    foreach ((array)$options as $key => $value) {
      if (is_string($value)) {
        $new_options[] = $value;
      }
      else {
        $new_options[] = $value['name'];
      }
    }
    return join("\n", $new_options);
  }
}
