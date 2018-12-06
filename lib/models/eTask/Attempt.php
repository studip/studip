<?php
namespace eTask;

/**
 * eTask conforming assignment attempt definition.
 *
 * @property int id database column
 * @property int assignment_id database column
 * @property string user_id database column
 * @property string start database column
 * @property string end database column
 * @property string options database column
 * @property eTask\Assignment assignment belongs_to etask\Assignment
 * @property JSONArrayobject options serialized database column
 */
class Attempt extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignment_attempts';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return array of StoredUserData objects
     */
    public static function getUserdata(\User $user)
    {
        $storage = new \StoredUserData($user);
        $sorm = self::findBySQL("user_id = ?", [$user->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('eTask Zuweisungen'), 'etask_assignment_attempts', $field_data, $user);
            }
        }
        return $storage;
    }
}
