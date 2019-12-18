<?php

namespace JsonApi\Models;

class ScheduleEntry extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'schedule';

        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        );

        parent::configure($config);
    }
}
