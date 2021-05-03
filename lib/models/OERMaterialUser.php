<?php

class OERMaterialUser extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_material_users';
        $config['belongs_to']['oeruser'] = [
            'class_name' => ExternalUser::class,
            'foreign_key' => 'user_id'
        ];
        parent::configure($config);
    }

    public function getJSON()
    {
        if ($this['external_contact']) {
            $user = $this['oeruser'];
            return [
                'user_id' => $user['foreign_user_id'],
                'name' => $user['name'],
                'avatar' => $user['avatar'],
                'description' => $user['description'],
                'host_url' => $user->host['url']
            ];
        } else {
            $user = User::find($this['user_id']);
            return [
                'user_id' => $user['user_id'],
                'name' => $user ? $user->getFullName() : _("unbekannt"),
                'avatar' => Avatar::getAvatar($user['user_id'])->getURL(Avatar::NORMAL),
                'description' => $user ? $user['oercampus_description'] : "",
                'host_url' => OERHost::thisOne()->url
            ];
        }
    }
}
