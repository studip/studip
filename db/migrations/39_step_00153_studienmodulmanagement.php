<?
class Step00153Studienmodulmanagement extends Migration
{
    function description ()
    {
        return 'adds field `type` to table `sem_tree`, adds new config entry';
    }

    function up ()
    {
        $db = DBManager::get();
        $name = 'SEM_TREE_SHOW_EMPTY_AREAS_PERM';
        $description = 'Bestimmt den globalen Nutzerstatus, ab dem in der Veranstaltungssuche auch Bereiche angezeigt werden, denen keine Veranstaltungen zugewiesen sind.';
        $time = time();

        $db->exec("
            INSERT INTO config
                (config_id, field, value, is_default, type, mkdate, chdate, description)
            VALUES
                (MD5('$name'), '$name', 'user', 1, 'string', $time, $time, '$description')
        ");
        $db->exec("ALTER TABLE `sem_tree` ADD `type` TINYINT UNSIGNED NOT NULL");
    }

    function down ()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM config WHERE field = 'SEM_TREE_SHOW_EMPTY_AREAS_PERM'");
        $db->exec("ALTER TABLE `sem_tree` DROP `type`");
    }
}
?>
