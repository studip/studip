<?
class Step00098UserDomains extends Migration
{
    function description () {
        return 'add database tables for user domains';
    }

    function up () {
        $db = DBManager::get();

        $db->exec("CREATE TABLE seminar_userdomains (
                      seminar_id varchar(32) NOT NULL default '',
                      userdomain_id varchar(32) NOT NULL default '',
                      PRIMARY KEY (seminar_id, userdomain_id)) ENGINE=MyISAM");
        $db->exec("CREATE TABLE user_userdomains (
                      user_id varchar(32) NOT NULL default '',
                      userdomain_id varchar(32) NOT NULL default '',
                      PRIMARY KEY (user_id, userdomain_id)) ENGINE=MyISAM");
        $db->exec("CREATE TABLE userdomains (
                      userdomain_id varchar(32) NOT NULL default '',
                      name varchar(255) NOT NULL default '',
                      PRIMARY KEY (userdomain_id)) ENGINE=MyISAM");

        $db->exec("ALTER TABLE auth_user_md5 CHANGE visible
                      visible enum('global','always','yes','unknown','no','never')
                      NOT NULL default 'unknown'");
    }

    function down () {
        $db = DBManager::get();

        $db->exec("DROP TABLE seminar_userdomains");
        $db->exec("DROP TABLE user_userdomains");
        $db->exec("DROP TABLE userdomains");

        $db->exec("ALTER TABLE auth_user_md5 CHANGE visible
                      visible enum('always','yes','unknown','no','never')
                      NOT NULL default 'unknown'");
    }
}
?>
