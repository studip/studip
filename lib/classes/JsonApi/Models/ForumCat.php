<?php

namespace JsonApi\Models;

/**
 * @property string category_id string primary key
 * @property string seminar_id string foreign key
 * @property string entry_name string database_column
 * @property string pos int
 */
class ForumCat extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'forum_categories';

        $config['belongs_to']['course'] = array(
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
        );
        parent::configure($config);
    }

    public static function getCategories(\Course $course)
    {
        return self::findBySQL('seminar_id = ? ORDER BY pos ASC', [$course->id]);
    }

    public function deleteCategory($categoryId, $seminarId)
    {
        //delete category...
        $stmt = \DBManager::get()->prepare('DELETE FROM
            forum_categories
            WHERE category_id = ?');
        $stmt->execute(array($categoryId));

        //... and set all it's entries to default category
        $stmt2 = \DBManager::get()->prepare('UPDATE
            forum_categories_entries
            SET category_id = ?, pos = 999
            WHERE category_id = ?');
        $stmt2->execute(array($seminarId, $categoryId));

        return $stmt && $stmt2;
    }
}
