<?
Class Tic4011RemoveTeilnehmerView extends Migration {

    function description()
    {
        return 'remove unused table teilnehmer_view';
    }


    function up()
    {
        DBManager::get()->exec("DROP TABLE `teilnehmer_view`");
    }

    function down()
    {
        ;
    }
}
