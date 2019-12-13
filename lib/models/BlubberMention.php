<?php
/**
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @since     4.5
 */

class BlubberMention extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'blubber_mentions';

        $config['belongs_to']['thread'] = [
            'class_name'        => BlubberThread::class,
            'foreign_key'       => 'thread_id',
            'assoc_foreign_key' => 'thread_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'        => User::class,
            'foreign_key'       => 'user_id',
            'assoc_foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }
}
