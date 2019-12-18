<?php

namespace JsonApi\Models\Resources;

class Category extends \SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_categories';

        parent::configure($config);
    }
}
