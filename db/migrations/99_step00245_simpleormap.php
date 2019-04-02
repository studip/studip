<?php
class Step00245Simpleormap extends Migration
{
    public function description()
    {
        return 'refreshes cache for SimpleORMap';
    }

    public function up()
    {
        SimpleORMap::expireTableScheme();
    }
}
