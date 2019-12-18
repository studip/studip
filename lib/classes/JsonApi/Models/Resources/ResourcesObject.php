<?php

namespace JsonApi\Models\Resources;

class ResourcesObject extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_objects';

        $config['belongs_to']['category'] = array(
            'class_name' => Category::class,
            'foreign_key' => 'category_id',
        );

        parent::configure($config);
    }

    public static function findAll($onlyRooms = false)
    {
        $query = 'SELECT resource_id
            FROM resources_objects AS ro
            LEFT JOIN resources_categories
            USING (category_id)
            WHERE ro.category_id != ""';

        if ($onlyRooms) {
            $query .= ' AND is_room = 1';
        }

        $query .= ' ORDER BY ro.name';

        $statement = \DBManager::get()->prepare($query);
        $statement->execute([]);

        return self::findMany($statement->fetchAll(\PDO::FETCH_COLUMN));
    }
}
