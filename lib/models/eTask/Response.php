<?php
namespace eTask;

/**
 * eTask conforming assignment definition.
 *
 *  @property int id database column
 *  @property int assignment_id database column
 *  @property int task_id database column
 *  @property string user_id database column
 *  @property string response database column
 *  @property int state database column
 *  @property float points database column
 *  @property string feedback database column
 *  @property string grader_id database column
 *  @property string mkdate database column
 *  @property string chdate database column
 *  @property string options database column
 *  @property eTask\Assignment assignment belongs_to etask\Assignment
 *  @property eTask\Task task belongs_to etask\Task
 *  @property User user belongs_to User
 *  @property User grader belongs_to User
 *  @property JSONArrayobject response serialized database column
 *  @property JSONArrayobject options serialized database column
 */
class Response extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_responses';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['belongs_to']['task'] = [
            'class_name' => $config['relationTypes']['Task'],
            'foreign_key' => 'task_id'
        ];

        $config['belongs_to']['user'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['belongs_to']['grader'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['serialized_fields']['response'] = 'JSONArrayObject';
        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('eTask Antworten'), 'etask_responses', $field_data);
            }
        }
    }
}
