<?php
/**
 * @author  Moritz Strohm <strohm@data-quest.de>
 * @license GPL2 or any later version
 */
class ArchiveHelpTexts extends Migration
{
    public function description()
    {
        return 'Adds / updates help texts for the controllers search/archive and course/archive.';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $db->exec("
        INSERT INTO help_content (global_content_id, content_id, language, content, route, studip_version)
        VALUES('55499281ce1a4757f17aaf73faa072ea', '55499281ce1a4757f17aaf73faa072ea', 'de', 'Auf dieser Seite können sie sich vor dem Archivieren vergewissern, das die richtige(n) Veranstaltunge(n) zum Archivieren ausgewählt wurden.', 'dispatch.php/course/archive/confirm', '4.0');
        ");
        $db->exec("
        INSERT INTO help_content (global_content_id, content_id, language, content, route, studip_version)
        VALUES('a2a649de15c8d8473b11fccc731dc80f', 'a2a649de15c8d8473b11fccc731dc80f', 'en', 'Before archiving you can check on this page that the right course(s) have been selected for archiving.', 'dispatch.php/course/archive/confirm', '4.0');
        ");
        $db->exec("
        UPDATE help_content SET route = 'dispatch.php/search/archive' WHERE route = 'archiv.php';
        ");
        
        $db = null;
    }

    public function down()
    {
        $db = DBManager::get();
        
        $db->exec("
        DELETE FROM help_content where route = 'dispatch.php/course/archive/confirm';
        ");
        $db->exec("
        UPDATE help_content SET route = 'archiv.php' WHERE route = 'dispatch.php/search/archive';
        ");
        
        $db = null;
    }
}
