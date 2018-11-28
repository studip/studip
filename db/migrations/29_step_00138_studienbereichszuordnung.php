<?php

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Step00138Studienbereichszuordnung extends Migration
{
    public function description() {
        return 'Adds the new Value SEM_TREE_ALLOW_BRANCH_ASSIGN to table config.';
    }

    public function up()
    {
        $this->announce("add new value SEM_TREE_ALLOW_BRANCH_ASSIGN to table config");

        $db = DBManager::get();
        $db->exec("INSERT IGNORE INTO `config` ".
                  "VALUES ('34f348c06bbd5d9fc7bb36a8d829e12e', '', ".
                  "'SEM_TREE_ALLOW_BRANCH_ASSIGN', '1', 1, 'boolean', 'global', ".
                  "'', 0, 1222947575, 1222947575, ".
                  "'Diese Option beeinflusst die Möglichkeit, Veranstaltungen ".
                  "entweder nur an die Blätter oder überall in der ".
                  "Veranstaltungshierarchie einhängen zu dürfen.', '', '')");

        $this->announce("done.");
    }


    public function down()
    {
        $this->announce("remove value SEM_TREE_ALLOW_BRANCH_ASSIGN from table config");

        DBManager::get()->exec("DELETE FROM `config` WHERE `field` = 'SEM_TREE_ALLOW_BRANCH_ASSIGN'");

        $this->announce("done.");
    }
}
