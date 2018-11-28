<?php

class Step00263InstGendering extends Migration {

    function description() {
        return 'Institutegroups can now be gendered';
    }

    function up() {
        DBManager::get()->exec("ALTER TABLE statusgruppen
            ADD (name_w varchar(255),
            name_m varchar(255));");
        Statusgruppen::expireTableScheme();
    }

    function down() {
        
    }

}

?>