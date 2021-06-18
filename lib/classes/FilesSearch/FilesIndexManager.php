<?php

namespace FilesSearch;

use DBManager;
use Log;
use PDOException;

/**
 * The FilesIndexManager is responsible for creating the fulltext
 * index of all or just single files.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class FilesIndexManager
{
    // max number of seconds creating the index is allowed to
    const TIME_LIMIT = 3600;

    // rating of a file's name in the index
    const RATING_FILE_REF_NAME = 0.9;

    // rating of a file's description in the index
    const RATING_FILE_REF_DESCRIPTION = 0.4;

    // rating of a file's author's name in the index
    const RATING_FILE_AUTHOR = 0.5;

    // rating of a course name in the index
    const RATING_RANGE_COURSE_NAME = 0.1;

    // rating of a institute name in the index
    const RATING_RANGE_INSTITUTE_NAME = 0.1;

    private static $log;
    private static $verbose = false;

    /**
     * (Re-)Create the fulltext index of all files or a single file.
     *
     * @param FileRef $fileRef optional; the file ref to (re-)index or
     *                         otherwise all files will be indexed
     * @param array   $options optional; an array of options, currently
     *                         only `verbose` is used - resulting in a
     *                         verbose description of what the
     *                         FilesIndexManager is doing
     *
     * @return int the number of seconds the indexing took
     */
    public static function sqlIndex(\FileRef $fileRef = null, array $options = [])
    {
        if (isset($options['verbose'])) {
            self::$verbose = $options['verbose'];
        }

        set_time_limit(self::TIME_LIMIT);
        $dbm = DBManager::get();
        $time = time();

        self::log('### Indexing started');

        try {
            // Purge DB
            $dbm->query('DROP TABLE IF EXISTS
                files_search_index_temp,
                files_search_index_old,
                files_search_attributes_temp,
                files_search_attributes_old');
            self::log('Database purged');

            // Create temporary tables
            $dbm->query('CREATE TABLE files_search_index_temp LIKE files_search_index');
            $dbm->query('CREATE TABLE files_search_attributes_temp LIKE files_search_attributes');
            self::log('Temporary tables created');

            if (isset($fileRef)) {
                self::log(sprintf('Index file %s', $fileRef->id));
                self::indexFile($fileRef);
            } else {
                self::log('Indexing files');
                self::indexFiles();
            }
            self::log('Finished indexing');

            // Swap tables
            $dbm->query('RENAME TABLE
                files_search_index           TO files_search_index_old,
                files_search_attributes      TO files_search_attributes_old,
                files_search_index_temp      TO files_search_index,
                files_search_attributes_temp TO files_search_attributes');
            self::log('Tables swapped');

            // Drop old index
            $dbm->query('DROP TABLE files_search_index_old, files_search_attributes_old');
            self::log('Old tables dropped');

            $runtime = time() - $time;
            self::log(sprintf('FINISHED! Runtime: %0d:%02d', floor($runtime / 60), $runtime % 60));

            // Return runtime
            return $runtime;

            // In case of mysql error imediately abort
        } catch (PDOException $e) {
            self::log('MySQL Error occured!');
            self::log($e->getMessage());
            var_dump($e);
            self::log('Aborting');
            throw $e;
        }
    }

    /**
     * This method indexes a single file.
     *
     * @param FileRef $fileRef the file to index
     */
    public static function indexFile(\FileRef $fileRef)
    {
        self::fillAttributes($fileRef);
        self::fillIndex($fileRef);
    }

    /**
     * This method indexes all files.
     */
    public static function indexFiles()
    {
        self::fillAttributes();
        self::fillIndex();
    }

    /**
     * This method indexes the direct children of a folder.
     *
     * @param Folder $folder the folder to index
     */
    public static function indexFolder(\Folder $folder)
    {
        $folder->file_refs->each(function (\FileRef $fileRef) {
            self::indexFile($fileRef);
        });
    }

    /**
     * This method drops all indexes of direct children of a folder.
     *
     * @param Folder $folder the folder to drop indexes of
     */
    public static function dropIndexForFolder(\Folder $folder)
    {
        $folder->file_refs->each(function (\FileRef $fileRef) {
            self::dropIndexForFile($fileRef);
        });
    }

    /**
     * This method drops the index of a single file.
     *
     * @param FileRef the file whose index shall be dropped
     */
    public static function dropIndexForFile(\FileRef $fileRef)
    {
        DBManager::get()->execute(
            'DELETE FROM files_search_index WHERE file_ref_id = :filerefid',
            [':filerefid' => $fileRef->id]
        );
        DBManager::get()->execute(
            'DELETE FROM files_search_attributes WHERE id = :filerefid',
            [':filerefid' => $fileRef->id]
        );
    }

    // Helpers

    /**
     * This method creates an index by using an SQL statement, some
     * params and an optional FileRef instance.
     *
     * The SQL statement is executed using the params. If the index
     * shall be created for a single FileRef, you have to specify it.
     *
     * @param string  $sql     the SQL statement
     * @param array   $params  the params to be used in the (prepared) SQL statement
     * @param FileRef $fileRef optional; if the index should be
     *                         created for this FileRef only
     */
    private static function createIndex($sql, $params, \FileRef $fileRef = null)
    {
        $table = isset($fileRef) ? 'files_search_index' : 'files_search_index_temp';
        $query = sprintf('INSERT INTO %s (file_ref_id, text, relevance) %s', $table, $sql);
        DBManager::get()->execute($query, $params);
    }

    private static function relevance($base, $modifier)
    {
        // 31556926 is the number of seconds in one year
        return "POW( $base , ((UNIX_TIMESTAMP() - $modifier ) / 31556926)) AS relevance";
    }

    /**
     * Logs an indexing event in the index.log file.
     *
     * @param type $info
     */
    private static function log($info)
    {
        if (!self::$verbose) {
            return;
        }

        if (!self::$log) {
            self::$log = self::createLogger();
        }
        self::$log->info(self::class.': '.$info);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private static function createLogger()
    {
        @unlink($GLOBALS['TMP_PATH'].'/files_index.log');
        Log::set('filesindexlog', $GLOBALS['TMP_PATH'].'/files_index.log');

        return Log::get('filesindexlog');
    }

    /**
     * This method fills the attributes table.
     *
     * If you do not specify a single FileRef instance, all the
     * fileRefs in the database will be used. Otherwise the attributes
     * table is filled with the attributes of that FileRef instance.
     *
     * @param FileRef $fileRef optional; if the attributes should be
     *                         filled for this FileRef only
     */
    private static function fillAttributes(\FileRef $fileRef = null)
    {
        if (isset($fileRef)) {
            $table = 'files_search_attributes';
            $where['sql'] = 'WHERE file_refs.id = :filerefid';
            $where['params'][':filerefid'] = $fileRef->id;
        } else {
            $table = 'files_search_attributes_temp';
            $where = ['sql' => '', 'params' => []];
        }

        $query = sprintf('
            INSERT INTO %s
                   (id, file_ref_user_id, file_ref_mkdate, file_ref_chdate,
                   folder_id, folder_range_id, folder_range_type, folder_type,
                   course_status, semester_start, semester_end)
            SELECT
                   file_refs.id,
                   file_refs.user_id,
                   file_refs.mkdate,
                   file_refs.chdate,

                   folders.id as folder_id,
                   folders.range_id AS folder_range_id,
                   folders.range_type AS folder_range_type,
                   folders.folder_type,

                   seminare.status AS course_status,
                   MIN(semester_data.beginn) AS semester_start,
                   MIN(semester_data.ende) AS semester_end
            FROM file_refs
                JOIN folders ON (file_refs.folder_id = folders.id)
                LEFT JOIN seminare ON (folders.range_type = \'course\' AND folders.range_id = seminare.Seminar_id)
                LEFT JOIN semester_courses ON (seminare.Seminar_id = semester_courses.course_id)
                LEFT JOIN semester_data ON (semester_courses.semester_id = semester_data.semester_id)
            %s
            GROUP BY file_refs.id
        ', $table, $where['sql']);

        DBManager::get()->execute($query, $where['params']);
    }

    private static function fillIndex(\FileRef $fileRef = null)
    {
        if (isset($fileRef)) {
            $whereCondition = 'WHERE file_refs.id = :filerefid';
            $whereParams = [':filerefid' => $fileRef->id];
        } else {
            $whereCondition = '';
            $whereParams = [];
        }

        // titel
        self::createIndex(
            sprintf(
                'SELECT file_refs.id, file_refs.name, %s FROM file_refs %s',
                self::relevance(
                    self::RATING_FILE_REF_NAME,
                    'file_refs.chdate'
                ),
                $whereCondition
            ),
            $whereParams,
            $fileRef
        );

        // beschreibung
        self::createIndex(
            sprintf(
                'SELECT file_refs.id, file_refs.description, %s FROM file_refs %s',
                self::relevance(
                    self::RATING_FILE_REF_DESCRIPTION,
                    'file_refs.chdate'
                ),
                $whereCondition
            ),
            $whereParams,
            $fileRef
        );

        // name des autors
        self::createIndex(
            sprintf(
                'SELECT file_refs.id,
                 CONCAT(auth_user_md5.Nachname,\' \', auth_user_md5.Vorname, \' \',
                        auth_user_md5.username), %s
                 FROM file_refs
                 JOIN auth_user_md5 ON (auth_user_md5.user_id = file_refs.user_id) %s',
                self::relevance(
                    self::RATING_FILE_AUTHOR,
                    'file_refs.chdate'
                ),
                $whereCondition
            ),
            $whereParams,
            $fileRef
        );

        $withRangeType = function ($rangeType) use ($whereCondition) {
            return sprintf(
                ' %s %s ',
                empty($whereCondition) ? 'WHERE' : $whereCondition.' AND ',
                sprintf('folders.range_type = \'%s\'', $rangeType)
            );
        };

        // name der veranstaltung
        self::createIndex(
            sprintf(
                'SELECT file_refs.id, seminare.Name, %s
                 FROM file_refs
                 JOIN folders ON (file_refs.folder_id = folders.id)
                 JOIN seminare ON (folders.range_id = seminare.Seminar_id)
                 %s',
                self::relevance(
                    self::RATING_RANGE_COURSE_NAME,
                    'file_refs.chdate'
                ),
                $withRangeType('course')
            ),
            $whereParams,
            $fileRef
        );

        // name der einrichtungen
        self::createIndex(
            sprintf(
                'SELECT file_refs.id, Institute.Name, %s
                 FROM file_refs
                 JOIN folders ON (file_refs.folder_id = folders.id)
                 JOIN Institute ON (folders.range_id = Institute.Institut_id)
                 %s',
                self::relevance(
                    self::RATING_RANGE_INSTITUTE_NAME,
                    'file_refs.chdate'
                ),
                $withRangeType('institute')
            ),
            $whereParams,
            $fileRef
        );
    }
}
