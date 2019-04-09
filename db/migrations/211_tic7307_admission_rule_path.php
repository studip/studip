<?php
class TIC7307AdmissionRulePath extends Migration
{
    public function up()
    {
        DBManager::get()->exec("ALTER TABLE `admissionrules` ADD `path` VARCHAR(255) NOT NULL");

        $stmt = DBManager::get()->prepare("UPDATE `admissionrules` SET `path` = :path WHERE `id` = :id");

        foreach (DBManager::get()->fetchAll("SELECT `id`, `ruletype` FROM `admissionrules` ORDER BY `id`") as $rule) {
            $stmt->execute([
                'path' => 'lib/admissionrules/' . strtolower($rule['ruletype']),
                'id' => $rule['id']
            ]);
        }

    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `questionnaires` DROP `path`");
    }
}
