<?php

class Tic1207Config extends Migration
{
    function description()
    {
        return 'adding sections to the configuration';
    }

    function up()
    {
        $db = DBManager::get();

        //changing sections
        $db->exec("UPDATE config SET section = 'resources' WHERE field LIKE 'RESOURCES%'");
        $db->exec("UPDATE config SET section = 'studygroups' WHERE field LIKE 'STUDYGROUP%'");
        $db->exec("UPDATE config SET section = 'elearning_interface' WHERE field LIKE 'ELEARNING\_INTERFACE%' AND field != 'ELEARNING_INTERFACE_ENABLE'");
        $db->exec("UPDATE config SET section = 'permissions' WHERE field IN ("
                 ."'ALLOW_ADMIN_USERACCESS','ALLOW_DOZENT_ARCHIV','ALLOW_DOZENT_VISIBILITY',"
                 ."'ALLOW_SELFASSIGN_INSTITUTE','AUX_RULE_ADMIN_PERM','LOCK_RULE_ADMIN_PERM',"
                 ."'INST_FAK_ADMIN_PERMS','RANGE_TREE_ADMIN_PERM','RESTRICTED_USER_MANAGEMENT',"
                 ."'SEM_CREATE_PERM','SEM_TREE_ADMIN_PERM','SEM_TREE_SHOW_EMPTY_AREAS_PERM',"
                 ."'SEM_VISIBILITY_PERM')");
        $db->exec("UPDATE config SET section = 'privacy' WHERE field IN ("
                 ."'DOZENT_ALWAYS_VISIBLE','ENABLE_SKYPE_INFO','FOAF_SHOW_IDENTITY',"
                 ."'FORUM_ANONYMOUS_POSTINGS','HOMEPAGE_VISIBILITY_DEFAULT',"
                 ."'USER_VISIBILITY_UNKNOWN')");
        $db->exec("UPDATE config SET section = 'files' WHERE field LIKE 'ZIP_%' OR "
                 ."field IN ('ENABLE_PROTECTED_DOWNLOAD_RESTRICTION', 'DOCUMENTS_EMBEDD_FLASH_MOVIES', "
                 ."'SENDFILE_LINK_MODE')");
        $db->exec("UPDATE config SET section = 'evaluation' WHERE field LIKE 'EVAL\_%'");
        $db->exec("UPDATE config SET section = 'archiv' WHERE field LIKE 'AUTO\_ARCHIV%'");

        //adding descriptions
        $db->exec("UPDATE config SET description = 'Schaltet ein oder aus, ob die Studiengruppen global verfügbar sind.' WHERE field = 'STUDYGROUPS_ENABLE'");
        $db->exec("UPDATE config SET description = 'Die Standardeinrichtung für Studiengruppen kann hier gesetzt werden.' WHERE field = 'STUDYGROUP_DEFAULT_INST'");
        $db->exec("UPDATE config SET description = 'Hier werden die globalen Einstellungen aller Studiengruppen hinterlegt.' WHERE field = 'STUDYGROUP_SETTINGS'");
        $db->exec("UPDATE config SET description = 'Hier werden die Nutzungsbedinungen der Studiengruppen hinterlegt.' WHERE field = 'STUDYGROUP_TERMS'");

        //adding missing types, if not existing, use default "string"
        $db->exec("UPDATE config SET type = 'string' WHERE type = ''");
        $db->exec("UPDATE config SET type = 'string' "
                 ."WHERE field LIKE 'ELEARNING\_INTERFACE%' "
                 ."AND field != 'ELEARNING_INTERFACE_ENABLE' "
                 ."AND field NOT LIKE '%ACTIVE' "
                 ."AND type = 'boolean'");
    }

    function down()
    {
    }
}
