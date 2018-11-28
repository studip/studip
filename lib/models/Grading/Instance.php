<?php

namespace Grading;

class Instance extends \SimpleORMap implements \PrivacyObject
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

    public function findByCourse(\Course $course)
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

    public function findByCourseAndUser(\Course $course, \User $user)
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

    /**
     * {@inheritdoc}
     */
    public static function getUserdata(\User $user)
    {
        $storage = new \StoredUserData($user);
        if ($instances = self::findBySql('user_id = ?', [$user->id])) {
            $fieldData = array_map(
                function ($instance) {
                    return
                        array_merge(
                            $instance->definition->toRawArray('course_id item name tool category weight'),
                            $instance->toRawArray('rawgrade feedback mkdate chdate')
                        );
                },
                $instances
            );
            if ($fieldData) {
                $storage->addTabularData('fach', $fieldData, $user);
            }
        }

        return [_('Leistungen') => $storage];
    }
}
