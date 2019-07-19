<?php

/**
 * Contact.class.php - model class for table contact
 *
 * @author      <mlunzena@uos.de>
 * @license GPL 2 or later
 * @property string contact_id database column
 * @property string id alias column for contact_id
 * @property string owner_id database column
 * @property string user_id database column
 * @property string buddy database column
 * @property string calpermission database column
 * @property SimpleORMapCollection group_assignments has_many StatusgruppeUser
 * @property User owner belongs_to User
 * @property User friend belongs_to User
 */
class Contact extends SimpleORMap
{

    protected static function configure($config = [])
    {

        $config['db_table'] = 'contact';
        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'owner_id'
        ];
        $config['belongs_to']['friend'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id'
        ];
        $config['has_many']['group_assignments'] = [
            'class_name'        => 'StatusgruppeUser',
            'assoc_func'        => 'findByContact',
            'foreign_key'       => function ($me) {
                return [$me];
            },
            'assoc_foreign_key' => function ($group, $params) {
                $group->setValue('user_id', $params[0]->user_id);
            },
            'on_store'          => 'store',
            'on_delete'         => 'delete'
        ];


        parent::configure($config);
    }
}
