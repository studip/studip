<?php
/**
 * Due a bug introduced in changeset 23768, the level column of the table
 * resouces_objects has not been set correctly. This migration will fix that.
 *
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 * @see     https://develop.studip.de/trac/ticket/5982
 */
class Biest5982FixResourcesObjectsLevel extends Migration
{
    public function description()
    {
        return 'Fix incorrect level values of "resouces_objects"; rebuilds correct hierarchy.';
    }
    
    public function up()
    {
        // Get all resources on a specific level. These will be used
        // as parent ids to find the children on the next level.
        $query = "SELECT `resource_id`
                  FROM `resources_objects`
                  WHERE `level` = :level";
        $parent_statement = DBManager::get()->prepare($query);

        // Update the level for all children of a set of parent ids.
        $query = "UPDATE `resources_objects`
                  SET `level` = :level
                  WHERE `parent_id` IN (:ids)";
        $child_statement = DBManager::get()->prepare($query);

        // Loop until the hierarchy has been built.
        $level = 0;
        do {
            // Read parent ids
            $parent_statement->bindValue(':level', $level);
            $parent_statement->execute();
            $parent_ids = $parent_statement->fetchAll(PDO::FETCH_COLUMN);

            // No parents, no children -> we're done.
            if (count($parent_ids) === 0) {
                break;
            }

            // Increase level
            $level = $level + 1;

            // Update level on all children, exit if no children have been
            // found/updated.
            $child_statement->bindValue(':level', $level);
            $child_statement->bindValue(':ids', $parent_ids, StudipPDO::PARAM_ARRAY);
            $updated_rows = $child_statement->execute();
        } while ($updated_rows > 0);
    }
    
    public function down()
    {
        // No, we don't need this.
    }
}
