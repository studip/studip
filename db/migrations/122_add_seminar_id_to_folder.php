<?php
# refers to https://develop.studip.de/trac/ticket/3973
class AddSeminarIdToFolder extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'add field `seminar_id` to table `folder`';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $this->addSeminarIdField();
        $this->updateHeads();
        $this->updateTails();
    }

    function addSeminarIdField()
    {
        DBManager::get()->exec('ALTER TABLE `folder` ADD `seminar_id` VARCHAR(32) NOT NULL AFTER `range_id`');
    }

    function updateHeads()
    {
        $this->updateFirstLevelFolders();
        $this->updateSpecialFolders();
    }

    function updateTails()
    {
        do {
            $updated = $this->updateSeminarIds("SELECT f1.folder_id, f2.seminar_id FROM `folder` f1 INNER JOIN folder f2 ON f1.range_id = f2.folder_id WHERE f1.seminar_id = ''");
        } while ($updated > 0);
    }

    function updateFirstLevelFolders()
    {
        $this->updateSeminarIds("SELECT f.folder_id, s.Seminar_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `seminare` s ON s.Seminar_id = f.range_id");
        $this->updateSeminarIds("SELECT f.folder_id, s.Seminar_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `seminare` s ON MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id");

        $this->updateSeminarIds("SELECT f.folder_id, i.Institut_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `Institute` i ON i.Institut_id = f.range_id");
        $this->updateSeminarIds("SELECT f.folder_id, i.Institut_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `Institute` i ON MD5(CONCAT(i.Institut_id, 'top_folder')) = f.range_id");
    }


    function updateSpecialFolders()
    {
        $this->updateSeminarIds("SELECT f.folder_id, s.range_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `statusgruppen` s ON s.statusgruppe_id = f.range_id");

        $this->updateSeminarIds("SELECT f.folder_id, t.seminar_id AS seminar_id ".
                          "FROM `folder` f ".
                          "INNER JOIN `themen` t ON t.issue_id = f.range_id");

        $this->updateSeminarIds("SELECT f.folder_id, a.user_id AS seminar_id
                            FROM `folder` f
                            INNER JOIN `auth_user_md5` a ON a.user_id = f.range_id");
    }

    function updateSeminarIds($sql)
    {
        $db = DBManager::get();

        $stmt = $db->prepare($sql);
        $stmt->execute([]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0;
        $stmt = $db->prepare("UPDATE folder SET seminar_id = ? WHERE folder_id = ?");
        foreach ($rows as $row) {
            $stmt->execute([$row['seminar_id'], $row['folder_id']]);
            $updated += $stmt->rowCount();
        }

        return $updated;
    }

    /**
     * revert this migration
     */
    function down()
    {
        $db = DBManager::get();

        try {
            $db->exec("ALTER TABLE `folder` DROP COLUMN `seminar_id`");
        } catch (Exception $e) { }
    }
}
