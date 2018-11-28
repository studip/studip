<?php
class Tic4463RemoveStm extends Migration
{
    function description()
    {
        return 'removes old studienmodule';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS `his_abschl`, `his_abstgv`, `his_pvers`, `his_stg`,
            `stm_abstract`, `stm_abstract_assign`, `stm_abstract_elements`,
            `stm_abstract_text`, `stm_abstract_types`, `stm_element_types`,
            `stm_instances`, `stm_instances_elements`, `stm_instances_text`");
        $db->exec("DELETE FROM config WHERE field = 'STM_ENABLE'");
    }

    function down()
    {
        /*
        Pleased to meet you, take my hand
        There is no way back from hell
        Pleased to meet you, say your prayers
        There is no way back from hell
        But I don't care
        No way back from hell ... YEAH!
        */
    }
}
