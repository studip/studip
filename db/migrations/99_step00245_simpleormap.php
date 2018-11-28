<?php

class Step00245Simpleormap extends Migration
{


    function description()
    {
        return 'refreshes cache for SimpleORMap';
    }

    function up()
    {
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
    }
}
