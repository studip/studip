<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class RemoveSkypeStatus extends Migration
{
    public function description()
    {
        return 'Removes user config entries for skype status';
    }

    public function up()
    {
        $query = "DELETE FROM `user_config`
                  WHERE `field` = 'SKYPE_ONLINE_STATUS'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // Nothing since there was not default entry for SKYPE_ONLINE_STATUS
    }
}
