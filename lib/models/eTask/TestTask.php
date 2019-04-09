<?php

namespace eTask;

/**
 * eTask conforming test task relation.
 *
 * @property int test_id database column
 * @property int task_id database column
 * @property int position database column
 * @property float points database column
 * @property string options database column
 * @property eTask\Test test belongs_to etask\Test
 * @property eTask\Task task belongs_to etask\Task
 * @property JSONArrayobject options serialized database column
 */
class TestTask extends \SimpleORMap
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_test_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['test'] = [
            'class_name' => $config['relationTypes']['Test'],
            'foreign_key' => 'test_id'];

        $config['belongs_to']['task'] = [
            'class_name' => $config['relationTypes']['Task'],
            'foreign_key' => 'task_id'];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }
}
