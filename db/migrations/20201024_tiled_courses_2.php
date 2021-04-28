<?php
class TiledCourses2 extends Migration
{
    public function description()
    {
        return 'Changes user config for open groups';
    }

    public function up()
    {
        ConfigValue::findEachByField(
            function ($value) {
                $open_groups = json_decode($value->value, true);
                $open_groups = array_keys($open_groups);
                $value->value = json_encode($open_groups);
                $value->store();
            },
            'MY_COURSES_OPEN_GROUPS'
        );
    }

    public function down()
    {
        ConfigValue::findEachByField(
            function ($value) {
                $open_groups = json_decode($value->value, true);
                $open_groups = array_fill_keys($open_groups, true);
                $value->value = json_encode($open_groups);
                $value->store();
            },
            'MY_COURSES_OPEN_GROUPS'
        );
    }
}
