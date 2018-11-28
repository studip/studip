<?php
class Step00126EmbeddingFlashMovies extends Migration {

    public function description ()
    {
        return 'Adds the new values EXTERNAL_FLASH_MOVIE_EMBEDDING and DOCUMENTS_EMBEDD_FLASH_MOVIES to table config.';
    }

    public function up ()
    {
        $this->announce("add new values EXTERNAL_FLASH_MOVIE_EMBEDDING and  to table config");

        DBManager::get()->exec("INSERT INTO `config` VALUES (MD5('EXTERNAL_FLASH_MOVIE_EMBEDDING'), '', 'EXTERNAL_FLASH_MOVIE_EMBEDDING', 'deny', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Sollen externe Flash-Filme mit Hilfe des [flash]-Tags der Schnellformatierung eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=image proxy benutzen', '', '')");

        DBManager::get()->exec("INSERT INTO `config` VALUES (MD5('DOCUMENTS_EMBEDD_FLASH_MOVIES'), '', 'DOCUMENTS_EMBEDD_FLASH_MOVIES', 'deny', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Sollen im Dateibereich Flash-Filme direkt in einem Player angezeigt werden? deny=nicht erlaubt, allow=erlaubt, autoload=Film wird beim aufklappen geladen (incrementiert Downloads), autoplay=Film wird sofort abgespielt', '', '')");

        $this->announce("done.");

    }

    public function down ()
    {
        $this->announce("remove values EXTERNAL_FLASH_MOVIE_EMBEDDING and DOCUMENTS_EMBEDD_FLASH_MOVIES from table config");

        DBManager::get()->exec("DELETE FROM `config` WHERE config_id IN(MD5('EXTERNAL_FLASH_MOVIE_EMBEDDING'), MD5('DOCUMENTS_EMBEDD_FLASH_MOVIES'))");

        $this->announce("done.");
    }
}