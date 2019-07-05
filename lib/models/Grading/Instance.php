<?php

namespace Grading;

class Instance extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'grading_instances';

        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['definition'] = [
            'class_name' => '\\Grading\\Definition',
            'foreign_key' => 'definition_id',
        ];

        parent::configure($config);
    }

    public static function findByCourse(\Course $course)
    {
        $definitionIds = Definition::findAndMapBySQL(
            function ($def) {
                return $def->id;
            },
            'course_id = ?',
            [$course->id]
        );

        if (!count($definitionIds)) {
            return [];
        }

        return self::findBySql('definition_id IN (?)', [$definitionIds]);
    }

    public static function findByCourseAndUser(\Course $course, \User $user)
    {
        $definitionIds = Definition::findAndMapBySQL(
            function ($def) {
                return $def->id;
            },
            'course_id = ?',
            [$course->id]
        );

        if (!count($definitionIds)) {
            return [];
        }

        return self::findBySql('definition_id IN (?) AND user_id = ?', [$definitionIds, $user->id]);
    }
}
