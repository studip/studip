<?php
/**
* some database tweaking
*
* @author  André Noack <noack@data-quest.de>
* @license GPL2 or any later version
*/

class Tic6025Performance extends Migration
{
    public function description()
    {
        return _('Performance Tweaks');
    }

    public function up()
    {
        $this->tryExecute("ALTER TABLE personal_notifications ADD INDEX (html_id)");
        $this->tryExecute("ALTER TABLE personal_notifications ADD INDEX (url(256))");

        $this->tryExecute("ALTER TABLE admission_seminar_user CHANGE status status ENUM('awaiting','accepted') NOT NULL");

        $this->tryExecute("ALTER TABLE ex_termine DROP INDEX autor_id");
        $this->tryExecute("ALTER TABLE ex_termine ADD INDEX (date)");

        $this->tryExecute("ALTER TABLE termine ADD INDEX (date)");

        $this->tryExecute("ALTER TABLE termin_related_groups DROP INDEX `unique`");
        $this->tryExecute("ALTER TABLE termin_related_groups DROP INDEX termin_id");
        $this->tryExecute("ALTER TABLE termin_related_groups DROP INDEX statusgruppe_id");
        $this->tryExecute("ALTER TABLE termin_related_groups CHANGE statusgruppe_id statusgruppe_id VARCHAR(32) NOT NULL");
        $this->tryExecute("ALTER TABLE termin_related_groups ADD PRIMARY KEY( termin_id, statusgruppe_id)");

        $this->tryExecute("ALTER TABLE `user_config` ADD INDEX (`field`, `value`(10))");

    }

    private function tryExecute($sql)
    {
        try {
            DBManager::get()->exec($sql);
        } catch (PDOException $e) {
            $this->announce("sql failed: %s", $sql);
        }
    }
}
