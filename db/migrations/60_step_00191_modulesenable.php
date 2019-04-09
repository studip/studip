<?php
class Step00191ModulesEnable extends Migration
{
    function description()
    {
        return 'renaming, adding and reorganisation of all studip core-modules into the database.';
    }

    private function getValue($field, $default = 1)
    {
        return isset($GLOBALS[$field]) ? (int) $GLOBALS[$field] : $default;
    }

    function getModules()
    {
        return [
            ['field' => 'CHAT_ENABLE',
                  'value' => $this->getValue('CHAT_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob der Chat global verfügbar ist.',
            ],
            ['field' => 'CALENDAR_ENABLE',
                  'value' => $this->getValue('CALENDAR_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob der Kalender global verfügbar ist.',
            ],
            ['field' => 'EXPORT_ENABLE',
                  'value' => $this->getValue('EXPORT_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob der Export global verfügbar ist.',
            ],
            ['field' => 'EXTERN_ENABLE',
                  'value' => $this->getValue('EXTERN_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob die externen Seiten global verfügbar sind.',
            ],
            ['field' => 'VOTE_ENABLE',
                  'value' => $this->getValue('VOTE_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob die Umfragen global verfügbar sind.',
            ],
            ['field' => 'ELEARNING_INTERFACE_ENABLE',
                  'value' => $this->getValue('ELEARNING_INTERFACE_ENABLE', 0),
                  'comment' => 'Schaltet ein oder aus, ob die Lernmodule global verfügbar sind.',
            ],
            ['field' => 'STM_ENABLE',
                  'value' => $this->getValue('STM_ENABLE', 0),
                  'comment' => 'Schaltet ein oder aus, ob die Studienmodule global verfügbar sind.',
            ],
            ['field' => 'WIKI_ENABLE',
                  'value' => $this->getValue('WIKI_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob das Wiki global verfügbar ist.',
            ],
            ['field' => 'SMILEYADMIN_ENABLE',
                  'value' => $this->getValue('SMILEYADMIN_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob die Administration der Smileys verfügbar ist.',
            ],
            ['field' => 'LOG_ENABLE',
                  'value' => $this->getValue('LOG_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob das Log global verfügbar ist.',
            ],
            ['field' => 'SCM_ENABLE',
                  'value' => $this->getValue('SCM_ENABLE'),
                  'comment' => 'Schaltet ein oder aus, ob freie Informationsseiten global verfügbar sind.',
            ],
            ['field' => 'BANNER_ADS_ENABLE',
                  'value' => $this->getValue('BANNER_ADS_ENABLE', 0),
                  'comment' => 'Schaltet ein oder aus, ob die Bannerwerbung global verfügbar ist.',
            ]
        ];
    }

    function up()
    {
        $db = DBManager::get();

        // existing
        $db->exec("UPDATE `config` SET `section` =  'modules', `chdate` = '".time()."' WHERE `field` = 'RESOURCES_ENABLE'");
        $db->exec("UPDATE `config` SET `section` =  'studygroups', `type` = 'boolean', `range` = 'global', `chdate` = '".time()."' WHERE `field` = 'STUDYGROUPS_ENABLE'");

        //moving
        foreach ($this->getModules() as $module) {
            $db->exec("INSERT IGNORE INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` ) VALUES ( MD5('". $module['field'] . "'), '', '". $module['field'] . "', '". $module['value'] . "', '1', 'boolean', 'global', 'modules', '0', '".time()."', '".time()."', '". $module['comment'] . "', '', '')");
        }

        // new
        $db->exec("INSERT IGNORE INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` ) VALUES ( MD5('LITERATURE_ENABLE'), '', 'LITERATURE_ENABLE', '1', '1', 'boolean', 'global', 'modules', '0', '".time()."', '".time()."', 'Schaltet ein oder aus, ob die Literaturverwaltung global verfügbar ist.', '', '')");

        //changing studygroup config
        $db->exec("UPDATE config SET value = REPLACE (value, '|', ' ') WHERE field = 'STUDYGROUP_SETTINGS'");
    }

    function down()
    {
        $db = DBManager::get();

        //existing
        $db->exec("UPDATE `config` SET `section` =  '', `chdate` = '".time()."' WHERE `field` = 'RESOURCES_ENABLE'");
        $db->exec("UPDATE `config` SET `section` =  '', `chdate` = '".time()."' WHERE `field` = 'STUDYGROUPS_ENABLE'");

        //moving
        foreach ($this->modules as $module) {
            $db->exec("DELETE FROM `config` WHERE `field` = '" . $module['field'] . "'");
        }

        //new
        $db->exec("DELETE FROM `config` WHERE `field` = 'LITERATURE_ENABLE'");

        //changing studygroup config
        $db->exec("UPDATE config SET value = REPLACE (value, ' ', '|') WHERE field = 'STUDYGROUP_SETTINGS'");
    }
}
