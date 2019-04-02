<?php
class Step00263InstGendering extends Migration
{
    public function description()
    {
        return 'Institutegroups can now be gendered';
    }

    public function up()
    {
        DBManager::get()->exec("ALTER TABLE statusgruppen
            ADD (name_w varchar(255),
            name_m varchar(255));");
    }
}
