<?php
class ScmAddPosition extends Migration
{
    public function description()
    {
        return 'Adds a new field "position" to the scm table in order to get '
              .'rid of an old, ugly-ish workaround that abused mkdate.';
    }

    public function up()
    {
        $query = "ALTER TABLE `scm`
                  ADD COLUMN `position` INT(11) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        $query = "UPDATE scm
                  SET position = :position
                  WHERE range_id = :range_id AND scm_id = :scm_id";
        $update_statement = DBManager::get()->prepare($query);

        $query = "SELECT range_id, scm_id FROM scm ORDER BY range_id ASC, mkdate ASC";
        $statement = DBManager::get()->query($query);

        $last_range = null;
        foreach ($statement as $row) {
            if ($row['range_id'] != $last_range) {
                $position = 0;
                $last_range = $row['range_id'];
            } else {
                $position += 1;
            }

            $update_statement->bindValue(':position', $position, PDO::PARAM_INT);
            $update_statement->bindValue(':range_id', $row['range_id']);
            $update_statement->bindValue(':scm_id', $row['scm_id']);
            $update_statement->execute();
        }
    }

    public function down()
    {
        $query = "ALTER TABLE `scm`
                  DROP COLUMN `position`";
        DBManager::get()->exec($query);
    }
}
