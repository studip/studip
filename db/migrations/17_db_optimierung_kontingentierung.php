<?php
class DbOptimierungKontingentierung extends Migration
{
    public function description ()
    {
        return 'adds keys in admission_seminar_studiengang, admission_seminar_user and seminar_user';
    }

    public function up ()
    {
        $this->announce("add keys...");

        $db = DBManager::get();
        $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $db->query("ALTER TABLE `admission_seminar_studiengang` ADD INDEX `studiengang_id` ( `studiengang_id` )");
        $db->query("ALTER TABLE `admission_seminar_user` ADD INDEX `seminar_id` ( `seminar_id`, `studiengang_id`, `status` )");
        $db->query("ALTER TABLE `seminar_user` ADD INDEX `Seminar_id` ( `Seminar_id`, `admission_studiengang_id` )");
        $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);

        $this->announce("done.");
    }

    public function down () 
    {
        $this->announce("delete keys...");

        $db = DBManager::get();
        $mode = $db->getAttribute(PDO::ATTR_ERRMODE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $db->query("ALTER TABLE `admission_seminar_studiengang` DROP INDEX `studiengang_id`");
        $db->query("ALTER TABLE `admission_seminar_user` DROP INDEX `seminar_id`");
        $db->query("ALTER TABLE `seminar_user` DROP INDEX `Seminar_id`");
        $mode = $db->setAttribute(PDO::ATTR_ERRMODE, $mode);

        $this->announce("done.");
    }
}
