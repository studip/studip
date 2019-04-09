<?php
/**
 * ForumCat.php - Class to handle categories for areas
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

class ForumCat extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'forum_categories';
        parent::configure($config);
    }

    /**
     * Return a list of all areas with their categories.
     * Empty categories are excluded by default
     *
     * @param string $seminar_id    the seminar_id the retrieve the categories for
     * @param string $exclude_null  if false, empty categories are returned as well
     * @return array list of categories
     */
    public static function getListWithAreas($seminar_id, $exclude_null = true)
    {
        $stmt = DBManager::get()->prepare("SELECT * FROM forum_categories AS fc
            LEFT JOIN forum_categories_entries AS fce USING (category_id)
            WHERE seminar_id = ? "
            . ($exclude_null ? 'AND fce.topic_id IS NOT NULL ' : '')
            . "ORDER BY fc.pos ASC, fce.pos ASC");

        $stmt->execute([$seminar_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns the name of the associated category for an area denoted by the
     * passed topic_id
     *
     * @param string $topic_id
     * @return string  the name of the category
     */
    public static function getCategoryNameForArea($topic_id)
    {
        $stmt = DBManager::get()->prepare("SELECT fc.entry_name FROM forum_categories AS fc
            LEFT JOIN forum_categories_entries AS fce USING (category_id)
            WHERE fce.topic_id = ?");
        $stmt->execute([$topic_id]);

        return $stmt->fetchColumn();
    }


    /**
     * Adds a new category with the passed name to the passed seminar and
     * returns the id of the newly created category
     *
     * @param string $seminar_id
     * @param string $name  the name of the new category
     * @return string  the id of the newly created category
     */
    public static function add($seminar_id, $name)
    {
        $stmt = DBManager::get()->prepare("INSERT INTO forum_categories
            (category_id, seminar_id, entry_name)
            VALUES (?, ?, ?)");

        $category_id = md5(uniqid(rand()));

        $stmt->execute([$category_id, $seminar_id, $name]);

        return $category_id;
    }


    /**
     * Remove the category with the passed id. The seminar_id is used only
     * to be certain.
     *
     * @param string $category_id  The ID of the category to be deleted
     * @param string $seminar_id  Seminar-ID the category belongs to
     */
    public static function remove($category_id, $seminar_id)
    {
        // delete the category itself
        $stmt = DBManager::get()->prepare("DELETE FROM
            forum_categories
            WHERE category_id = ?");
        $stmt->execute([$category_id]);

        // set all entries to default category
        $stmt = DBManager::get()->prepare("UPDATE
            forum_categories_entries
            SET category_id = ?, pos = 999
            WHERE category_id = ?");
        $stmt->execute([$seminar_id, $category_id]);
    }


    /**
     * Set the position for the passed category to the passed value
     *
     * @param string $category_id  the ID of the category to update
     * @param int $pos             the new position
     */
    public static function setPosition($category_id, $pos)
    {
        $stmt = DBManager::get()->prepare("UPDATE
            forum_categories
            SET pos = ? WHERE category_id = ?");
        $stmt->execute([$pos, $category_id]);
    }


    /**
     * Add the passed area to the passed category and remove it from all
     * other categories.
     *
     * @param string $category_id  the ID of the category
     * @param string $area_id      the ID of the area to add the category to
     */
    public static function addArea($category_id, $area_id)
    {
        // remove area from all other categories
        $stmt = DBManager::get()->prepare("DELETE FROM
            forum_categories_entries
            WHERE topic_id = ?");
        $stmt->execute([$area_id]);

        // add area to this category, make sure it is at the end
        $stmt = DBManager::get()->prepare("SELECT COUNT(*) FROM
            forum_categories_entries
            WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $new_pos = $stmt->fetchColumn() + 1;

        $stmt = DBManager::get()->prepare("REPLACE INTO
            forum_categories_entries
            (category_id, topic_id, pos) VALUES (?, ?, ?)");
        $stmt->execute([$category_id, $area_id, $new_pos]);
    }


    /**
     * Remove the passed area from all categories.
     *
     * @param string $area_id  the ID of the area to be removed
     */
    public static function removeArea($area_id)
    {
        $stmt = DBManager::get()->prepare("DELETE FROM
            forum_categories_entries
            WHERE topic_id = ?");
        $stmt->execute([$area_id]);
    }


    /**
     * Set the position for the passed category to the passed value
     *
     * @param string $area_id  the ID of the area to update
     * @param int    $pos      the new position
     */
    public static function setAreaPosition($area_id, $pos)
    {
        $stmt = DBManager::get()->prepare("UPDATE
            forum_categories_entries
            SET pos = ? WHERE topic_id = ?");
        $stmt->execute([$pos, $area_id]);
    }


    /**
     * Set the name for the passed category
     *
     * @param string $category_id  the ID of the category to update
     * @param string $name         the name to set
     */
    public static function setName($category_id, $name)
    {
        $stmt = DBManager::get()->prepare("UPDATE
            forum_categories
            SET entry_name = ? WHERE category_id = ?");
        $stmt->execute([$name, $category_id]);
    }

    /**
     * Return the data for the passed category_id
     *
     * @param type $category_id
     *
     * @return array the data for the passed category_id
     */
    public static function get($category_id)
    {
        $stmt = DBManager::get()->prepare("SELECT * FROM forum_categories
            WHERE category_id = ?");
        $stmt->execute([$category_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return the areas for the passed category_id
     *
     * @param type $category_id
     * @param type $start  limit start (optional)
     * @param type $limit  number of entries to fetch (optional, default is 20)
     *
     * @return array the data for the passed category_id
     */
    public static function getAreas($category_id, $start = null, $num = 20)
    {
        $category = self::get($category_id);

        $limit = '';
        if ($start !== null && $num) {
            $limit = " LIMIT $start, $num";
        }

        if ($category_id == $category['seminar_id']) {
            $stmt = DBManager::get()->prepare("SELECT fe.* FROM forum_entries AS fe
                LEFT JOIN forum_categories_entries AS fce USING (topic_id)
                WHERE seminar_id = ? AND depth = 1 AND (
                    fce.category_id = ? OR fce.category_id IS NULL
                ) ORDER BY category_id DESC, pos ASC" . $limit);
            $stmt->execute([$category_id, $category_id]);
        } else {
            $stmt = DBManager::get()->prepare("SELECT forum_entries.* FROM forum_categories_entries
                LEFT JOIN forum_entries USING(topic_id)
                WHERE category_id = ?
                ORDER BY pos ASC" . $limit);

            $stmt->execute([$category_id]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
