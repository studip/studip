<?php
/**
 * @author Moritz Strohm <strohm@data-quest.de>
 */
class Tic8773SortNewsByChdate extends Migration
{
    public function up()
    {
        $query = "INSERT INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES (
                    'SORT_NEWS_BY_CHDATE', 'false', 'boolean', 'global', 'view',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Wenn diese Einstellung gesetzt ist werden Ankündigungen nach ihrem letzten Änderungsdatum statt ihrem Erstellungsdatum sortiert angezeigt.'
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DELETE FROM config WHERE field = 'SORT_NEWS_BY_CHDATE'";
        DBManager::get()->exec($query);
    }
}
