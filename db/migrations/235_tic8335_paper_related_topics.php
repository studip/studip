<?php
/**
 * This migration adds the required database column that allows teachers to
 * flag a topic as "paper related" ("Hausarbeit/Referat") and create groups
 * especially for these topics.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
class Tic8335PaperRelatedTopics extends Migration
{
    public function up()
    {
        if ($this->hasColumn()) {
            return;
        }

        $query = "ALTER TABLE `themen`
                    ADD COLUMN `paper_related` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `priority`";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        if (!$this->hasColumn()) {
            return;
        }

        $query = "ALTER TABLE `themen` DROP COLUMN `paper_related`";
        DBManager::get()->exec($query);
    }

    private function hasColumn()
    {
        $query = "SHOW COLUMNS FROM `themen`";
        $columns = DBManager::get()->fetchFirst($query);

        return in_array('paper_related', $columns);
    }
}
