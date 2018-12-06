<?php

namespace eTask;

/**
 * eTask conforming task definition.
 *
 * @property int id database column
 * @property string type database column
 * @property string title database column
 * @property string description database column
 * @property string task database column
 * @property string user_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string options database column
 * @property User owner belongs_to User
 * @property SimpleORMapCollection tests additional field etask\Test
 * @property SimpleORMapCollection test_tasks has_many etask\TestTask
 * @property SimpleORMapCollection responses has_many etask\Response
 * @property JSONArrayobject task serialized database column
 * @property JSONArrayobject options serialized database column
 */
class Task extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['owner'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['additional_fields']['tests']['get'] = 'getTests';

        $config['has_many']['test_tasks'] = [
            'class_name' => $config['relationTypes']['TestTask'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['responses'] = [
            'class_name' => $config['relationTypes']['Response'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['task'] = 'JSONArrayObject';
        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    /**
     * Retrieve the tests associated to this task.
     *
     * @return SimpleORMapCollection the associated tests
     */
    public function getTests()
    {
        $klass = $this->relationTypes['Test'];

        return \SimpleORMapCollection::createFromArray(
            $klass::findThru(
                $this->id,
                [
                    'thru_table' => 'etask_test_tasks',
                    'thru_key' => 'task_id',
                    'thru_assoc_key' => 'test_id',
                    'assoc_foreign_key' => 'id',
                    'order_by' => 'ORDER BY etask_tests.chdate ASC'
                ]
            )
        );
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return StoredUserData object
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
                $storage->addTabularData(_('eTask Aufgaben'), 'etask_tasks', $field_data, $user);
            }
        }

        $field_data = \DBManager::get()->fetchAll("SELECT * FROM etask_task_tags WHERE user_id =?", [$user->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('eTask Aufgaben Tags'), 'etask_task_tags', $field_data, $user);
        }
        return $storage;
    }
}
