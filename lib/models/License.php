<?php

class License extends SimpleORMap
{
    public static function findDefault()
    {
        return static::findOneBySQL("`default` = '1'");
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'licenses';
        parent::configure($config);
    }
}
