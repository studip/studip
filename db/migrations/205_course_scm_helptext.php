<?php
/**
 * @author  Moritz Strohm <strohm@data-quest.de>
 * @license GPL2 or any later version
 */
class CourseScmHelptext extends Migration
{
    public function description()
    {
        return 'Adds a help text for the free information page in a course.';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $db->exec("
            INSERT INTO help_content (global_content_id, content_id, language, content, route, studip_version)
            VALUES
            ('142482b4b06a376b2eb4c91d38559a15', '142482b4b06a376b2eb4c91d38559a15', 'de', 'Freie Gestaltung von Reiternamen und Inhalten durch Lehrende. Es gibt Raum für eigene Informationen, der Name des Reiters ist frei definierbar. Es können beliebig viele Einträge (\"neue Einträge\") hinzugefügt werden.', 'dispatch.php/course/scm', '4.0')
            ;"
        );
        
        $db = null;
    }

    public function down()
    {
        $db = DBManager::get();
        
        $db->exec(
            "DELETE FROM help_content WHERE content_id = '142482b4b06a376b2eb4c91d38559a15';"
        );
        
        $db = null;
    }
}
