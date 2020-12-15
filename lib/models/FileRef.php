<?php
/**
 * FileRef.php
 * model class for table file_refs
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 *
 * @property string id database column
 * @property string file_id database column
 * @property string folder_id database column
 * @property string user_id database column
 * @property string name database column
 * @property string downloads database column
 * @property string description database column
 * @property string license database column
 * @property string content_terms_of_use_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMap file belongs_to File
 * @property SimpleORMap folder belongs_to Folder
 * @property SimpleORMap owner belongs_to User
 * @property SimpleORMap terms_of_use belongs_to ContentTermsOfUse
 */
class FileRef extends SimpleORMap implements PrivacyObject, FeedbackRange
{

    protected $folder_type;
    protected $download_url;
    public $path_to_blob;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'file_refs';
        $config['belongs_to']['file'] = [
            'class_name'  => 'File',
            'foreign_key' => 'file_id',
        ];
        $config['belongs_to']['folder'] = [
            'class_name'  => 'Folder',
            'foreign_key' => 'folder_id',
        ];
        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['terms_of_use'] = [
            'class_name' => 'ContentTermsOfUse',
            'foreign_key' => 'content_terms_of_use_id',
            'assoc_func' => 'findOrBuild'
        ];

        $config['additional_fields']['size'] = ['file', 'size'];
        $config['additional_fields']['mime_type'] = ['file', 'mime_type'];
        $config['additional_fields']['download_url']['set'] = 'setDownloadURL';
        $config['additional_fields']['download_url']['get'] = 'getDownloadURL';
        $config['additional_fields']['author_name']['get'] = 'getAuthorName';
        $config['additional_fields']['is_link']['get'] = 'isLink';
        $config['additional_fields']['foldertype']['set'] = 'setFolderType';
        $config['additional_fields']['foldertype']['get'] = 'getFolderType';

        $config['registered_callbacks']['after_delete'][] = 'cbRemoveFileIfOrphaned';
        $config['registered_callbacks']['after_delete'][] = 'cbRemoveFeedbackElements';
        $config['registered_callbacks']['before_store'][] = 'cbMakeUniqueFilename';

        parent::configure($config);
    }

    /**
     * This callback is called after deleting a FileRef.
     * It removes the File object that is associated with the FileRef,
     * if the File is not referenced by any other FileRef object.
     */
    public function cbRemoveFileIfOrphaned()
    {
        if (!self::countBySql('file_id = ?', [$this->file_id])) {
            File::deleteBySQL("id = ?", [$this->file_id]);
        }
    }

    /**
     * This callback is called after deleting a FileRef.
     * It removes feedback elements that are associated with the FileRef.
     */
    public function cbRemoveFeedbackElements()
    {
        FeedbackElement::deleteBySQL("range_id = ? AND range_type = 'FileRef'", [$this->id]);
    }

    /**
     * This callback is called before storing a FileRef object.
     * In case the name field is changed this callback assures that the
     * name of the FileRef is unique inside the folder where
     * the FileRef is placed.
     */
    public function cbMakeUniqueFilename()
    {
        if (isset($this->folder) && $this->isFieldDirty('name')) {
            $this->name = $this->folder->getUniqueName($this->name);
        }
        if ($this->isFieldDirty('folder_id')) {
            //We have moved the file ref. $this->folder may not work directly
            //so we have to load the folder manually:
            $folder = Folder::find($this->folder_id);
            if ($folder) {
                $this->name = $folder->getUniqueName($this->name);
            }
        }
    }

    /**
     * Overrides the usual download url that this file_ref would get by the system (sendfile.php...)
     * Use this method by cloud plugins.
     * If you set download URL to null, the normal sendfile.php will be set as default download URL.
     * @param $url : string as URL or null to set URL to sendfile.php-URL
     */
    public function setDownloadURL($field, $url) {
        $this->download_url = $url;
    }

    /**
     * Returns the download-URL for the FileRef.
     *
     * @param string $dltype The download type: 'normal', 'zip', 'force' or 'force_download'.
     *
     * @return string The URL for the FileRef.
     */
    public function getDownloadURL($dltype = 'normal')
    {
        if ($this->download_url) {
            return $this->download_url;
        }
        $mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
        $link = [];
        $params = [];
        $type = '0';
        $file_name = $this->name;
        $file_id = $this->id;

        switch($mode) {
            case 'rewrite':
                $link[] = 'download/';
                switch ($dltype) {
                    case 'zip':
                        $link[] = 'zip/';
                        break;
                    case 'force':
                    case 'force_download':
                        $link[] = 'force_download/';
                        break;
                    default:
                        $link[] = 'normal/';
                }
                $link[] = $type . '/' . $file_id . '/' . $file_name;
                break;
            default:
                $link[] = 'sendfile.php';
                if ($dltype == 'zip') {
                    $params['zip'] = 1;
                } elseif (in_array($dltype,  ['force_download', 'force'])) {
                    $params['force_download'] = 1;
                }
                $params['type'] = $type;
                $params['file_id'] = $file_id;
                $params['file_name'] = $file_name;
        }
        return URLHelper::getScriptURL(implode('', $link), $params);
    }

    /**
     * Returns the name of the FileRef's author.
     *
     * @return string The name of the FileRef's author.
     */
    public function getAuthorName()
    {
        if (isset($this->owner)) {
            return $this->owner->getFullName('no_title_rev');
        }
        return $this->file->author_name;
    }

    /**
     * This method increments the download counter of the FileRef.
     *
     * @return The number of rows of the file_refs table that have been altered.
     */
    public function incrementDownloadCounter()
    {
        $this->downloads += 1;
        if (!$this->isNew()) {
            $where_query = join(' AND ' , $this->getWhereQuery());
            $query = "UPDATE `{$this->db_table}`
                      SET `downloads` = `downloads` + 1
                      WHERE {$where_query}";
            return DBManager::get()->exec($query);
        }
    }

    /**
     * Returns the license object for this file.
     *
     * @return Object (to be specified!)
     */
    public function getLicenseObject()
    {
        if (class_exists($this->license)) {
            return new $this->license();
        }
        throw new UnexpectedValueException("class: {$this->license} not found");
    }

    /**
     * Determines whether this FileRef is a link or not.
     * @deprecated
     * @return bool True, if the FileRef references a link, false otherwise.
     */
    public function isLink()
    {
        return false;
    }

    public function setFolderType($field, FolderType $folder_type)
    {
        $this->folder_type = $folder_type;
    }

    public function getFolderType()
    {
        if ($this->folder_type) {
            return $this->folder_type;
        } elseif($this->folder) {
            return $this->folder->getTypedFolder();
        }
    }

    /**
     * Determines if the FileRef references an image file.
     *
     * @return bool True, if the referenced file is an image file, false otherwise.
     */
    public function isImage()
    {
        return mb_strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Determines if the FileRef references an audio file.
     *
     * @return bool True, if the referenced file is an audio file, false otherwise.
     */
    public function isAudio()
    {
        return mb_strpos($this->mime_type, 'audio/') === 0;
    }


    /**
     * Determines if the FileRef references a video file.
     *
     * @return bool True, if the referenced file is a video file, false otherwise.
     */
    public function isVideo()
    {
        return mb_strpos($this->mime_type, 'video/') === 0;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Dateien'), 'file_refs', $field_data);
            }
        }
    }

    /**
     * @returns FileType
     */
    public function getFileType()
    {
        $filetype = $this->file->filetype;
        if (class_exists($filetype) && is_subclass_of($filetype, 'FileType')) {
            return new $filetype($this);
        } else {
            return new UnknownFileType($this);
        }
    }

    public function getRangeName()
    {
        return $this->name;
    }

    public function getRangeIcon($role)
    {
        return FileManager::getIconForFileRef($this, $role);
    }

    public function getRangeUrl()
    {
        return 'course/files/index/' . $this->foldertype->getId();
    }

    public function getRangeCourseId()
    {
        return $this->foldertype->range_id;
    }

    public function isRangeAccessible(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;

        return $this->foldertype->isFileDownloadable($this->id, $user_id);
    }


    /**
     * This is a helper method to generate the SQL query for the
     * findAll and countAll methods. Relevant FileRef objects for these methods
     * can be limited by a time range and a specific course. The amount
     * and the offset of the FileRef objects to be retrieved can also be set.
     *
     * @param string $user_id The ID of the user that is used to count or
     *     retrieve FileRef objects.
     *
     * @param DateTime|null $begin The begin of the time range.
     *
     * @param DateTime|null $end The end of the time range.
     *
     * @param string $course_id The ID of the course from which FileRef objects
     *     shall be retrieved.
     *
     * @param int $file_limit The maximum amount of FileRef objects to be
     *     retrieved.
     *
     * @param int $file_offset The offset in the FileRef data from which to
     *     start retrieving objects.
     *
     * @returns mixed[] An array with two indexes:
     *     - sql: The SQL query string.
     *     - params: An array with SQL query parameters.
     */
    protected static function getAllSql($user_id, $begin = null, $end = null, $course_id = '', $file_limit = 0, $file_offset = 0)
    {
        $sql = '';
        $sql_params = [];
        if ($course_id) {
            //Limit the result for a specific course.
            $sql = 'INNER JOIN `folders`
                ON `file_refs`.`folder_id` = `folders`.`id`
                WHERE `folders`.`range_id` =  :course_id ';
            $sql_params['course_id'] = $course_id;
            if (($begin instanceof DateTime) && ($end instanceof DateTime)) {
                $sql .= 'AND `file_refs`.`chdate` BETWEEN :begin AND :end ';
                $sql_params['begin'] = $begin->getTimestamp();
                $sql_params['end'] = $end->getTimestamp();
            }
        } else {
            if ($GLOBALS['perm']->have_perm('root', $user_id)) {
                //This is easy: Get all recent files ordered by chdate.
                if (($begin instanceof DateTime) && ($end instanceof DateTime)) {
                    $sql = 'chdate BETWEEN :begin AND :end ';
                    $sql_params['begin'] = $begin->getTimestamp();
                    $sql_params['end'] = $end->getTimestamp();
                } else {
                    $sql = 'TRUE ';
                }
            } else {
                //Get the folders (all folders) of the user first and then
                //check for new files. This means getting all folders of courses
                //and institutes where the user has access to.

                $range_ids = FileManager::getRangeIdsForFolders($user_id, true);

                //Now get all file refs:
                $sql = 'INNER JOIN `folders`
                    ON `file_refs`.`folder_id` = `folders`.`id`
                    WHERE `folders`.`range_id` IN ( :range_ids ) ';
                $sql_params['range_ids'] = $range_ids;

                if (($begin instanceof DateTime) && ($end instanceof DateTime)) {
                    $sql .= 'AND `file_refs`.`chdate` BETWEEN :begin AND :end ';
                    $sql_params['begin'] = $begin->getTimestamp();
                    $sql_params['end'] = $end->getTimestamp();
                }
            }
        }
        $sql .= ' ORDER BY `file_refs`.`chdate` DESC, `file_refs`.`mkdate` DESC, `file_refs`.`name` ASC';
        if ($file_limit > 0) {
            $sql .= ' LIMIT :limit';
            $sql_params['limit'] = $file_limit;
            if ($file_offset > 0) {
                $sql .= ' OFFSET :offset';
                $sql_params['offset'] = $file_offset;
            }
        }
        return ['sql' => $sql, 'params' => $sql_params];
    }


    /**
     * This method retrieves the FileRef objects a user has access to.
     *
     * @see FileRef::getAllSql for the parameter description.
     *
     * @returns FileRef[] A list of FileRef objects.
     */
    public static function findAll($user_id, $begin = null, $end = null, $course_id = null, $file_limit = 0, $file_offset = 0)
    {
        $sql_data = self::getAllSql($user_id, $begin, $end, $course_id, $file_limit, $file_offset);
        return FileRef::findBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * This method counts all the FileRef objects a user has access to.
     *
     * @see FileRef::getAllSql for the parameter description.
     *
     * @returns int The amount of found FileRef rows.
     */
    public static function countAll($user_id, $begin = null, $end = null, $course_id = null)
    {
        $sql_data = self::getAllSql($user_id, $begin, $end, $course_id);
        return FileRef::countBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * This is a helper method to generate the SQL query for the
     * findPublicFiles and countPublicFiles methods. Relevant FileRef objects
     * for these methods can be limited by a time range. The amount
     * and the offset of the FileRef objects to be retrieved can also be set.
     *
     * @param string $user_id The ID of the user that is used to count or
     *     retrieve FileRef objects in public folders.
     *
     * @param DateTime|null $begin The begin of the time range.
     *
     * @param DateTime|null $end The end of the time range.
     *
     * @param int $file_limit The maximum amount of FileRef objects to be
     *     retrieved from public folders.
     *
     * @param int $file_offset The offset in the FileRef data from which to
     *     start retrieving objects from public folders.
     *
     * @returns mixed[] An array with two indexes:
     *     - sql: The SQL query string.
     *     - params: An array with SQL query parameters.
     */
    protected static function getPublicFilesSql($user_id, $begin = null, $end = null, $file_limit = 0, $file_offset = 0)
    {
        $sql = "INNER JOIN `folders`
            ON `file_refs`.`folder_id` = `folders`.`id`
            WHERE `folders`.`folder_type` = 'PublicFolder'
            AND `folders`.`range_type` = 'user' ";
        $sql_params = [];
        if ($user_id) {
            $sql .= " AND `folders`.`user_id` = :user_id";
            $sql_params['user_id'] = $user_id;
        }
        if (($begin instanceof DateTime) && ($end instanceof DateTime)) {
            $sql .= ' AND `file_refs`.`chdate` BETWEEN :begin AND :end';
            $sql_params['begin'] = $begin->getTimestamp();
            $sql_params['end'] = $end->getTimestamp();
        }
        $sql .= " ORDER BY `file_refs`.`chdate` DESC";
        if ($file_limit > 0) {
            $sql .= " LIMIT :file_limit";
            $sql_params['file_limit'] = $file_limit;
        }
        if ($file_offset > 0) {
            $sql .= " OFFSET :file_offset";
            $sql_params['file_offset'] = $file_offset;
        }
        return ['sql' => $sql, 'params' => $sql_params];
    }


    /**
     * This method retrieves the FileRef objects in public folders which
     * can be accessed by a user.
     *
     * @see FileRef::getPublicFilesSql for the parameter description.
     *
     * @returns FileRef[] A list of FileRef objects.
     */
    public static function findPublicFiles($user_id, $begin = null, $end = null, $file_limit = 0, $file_offset = 0)
    {
        $sql_data = self::getPublicFilesSql($user_id, $begin, $end, $file_limit, $file_offset);
        return self::findBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * This method counts the FileRef objects in public folders which
     * can be accessed by a user.
     *
     * @see FileRef::getPublicFilesSql for the parameter description.
     *
     * @returns int The amount of found FileRef rows.
     */
    public static function countPublicFiles($user_id, $begin = null, $end = null)
    {
        $sql_data = self::getPublicFilesSql($user_id, $begin, $end);
        return self::countBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * This is a helper method to generate the SQL query for the
     * findUploadedFiles and countUploadedFiles methods. Relevant FileRef
     * objects for these methods can be limited by a time range and a specified
     * course. Furthermore, it is possible to limit the search to FileRef
     * objects without a license set or with the "unknown" license set.
     * The amount and the offset of the FileRef objects to be retrieved can also
     * be set.
     *
     * @param string $user_id The ID of the user whose uploaded files shall be
     *     retrieved or counted.
     *
     * @param DateTime|null $begin The begin of the time range.
     *
     * @param DateTime|null $end The end of the time range.
     *
     * @param string $course_id The ID of the course from which FileRef objects
     *     shall be retrieved.
     *
     * @param bool $unknown_license_only Whether to only query for FileRef
     *     objects without a license or with the "unknown" license set
     *     (true) or to query all uploaded FileRef objects (false).
     *     Defaults to false.
     *
     * @param int $file_limit The maximum amount of FileRef objects to be
     *     retrieved from public folders.
     *
     * @param int $file_offset The offset in the FileRef data from which to
     *     start retrieving objects from public folders.
     *
     * @returns mixed[] An array with two indexes:
     *     - sql: The SQL query string.
     *     - params: An array with SQL query parameters.
     */
    protected static function getUploadedFilesSql($user_id, $begin = null, $end = null, $course_id = '', $unknown_license_only = false, $file_limit = 0, $file_offset = 0)
    {
        $sql = '';
        if ($course_id) {
            $sql = 'INNER JOIN `folders`
                ON `file_refs`.`folder_id` = `folders`.`id`
                WHERE ';
        }
        $sql .= "`file_refs`.`user_id` = :user_id";
        $sql_params = [
            'user_id' => $user_id
        ];
        if ($unknown_license_only) {
            $sql .= " AND (
                `file_refs`.`content_terms_of_use_id` IN ('', 'UNDEF_LICENSE')
                OR
                `file_refs`.`content_terms_of_use_id` IS NULL
            )";
        }

        if (($begin instanceof DateTime) && ($end instanceof DateTime)) {
            $active_sidebar_filters[] = 'course';
            $sql .= ' AND `file_refs`.`chdate` BETWEEN :begin AND :end ';
            $sql_params['begin'] = $begin->getTimestamp();
            $sql_params['end'] = $end->getTimestamp();
        }
        if ($course_id) {
            $sql .= ' AND `folders`.`range_id` = :course_id';
            $sql_params['course_id'] = $course_id;
        }
        $sql .= " ORDER BY `file_refs`.`chdate` DESC";
        if ($file_limit > 0) {
            $sql .= " LIMIT :file_limit";
            $sql_params['file_limit'] = $file_limit;
        }
        if ($file_offset > 0) {
            $sql .= " OFFSET :file_offset";
            $sql_params['file_offset'] = $file_offset;
        }
        return ['sql' => $sql, 'params' => $sql_params];
    }


    /**
     * Retrieves all uploaded files a user has uploaded.
     *
     * @see FileRef::getUploadedFilesSql for the parameter description.
     *
     * @returns FileRef[] A list of FileRef objects for files the user uploaded.
     */
    public static function findUploadedFiles($user_id, $begin = null, $end = null, $course_id = '', $unknown_license_only = false, $file_limit = 0, $file_offset = 0)
    {
        $sql_data = self::getUploadedFilesSql($user_id, $begin, $end, $course_id, $unknown_license_only, $file_limit, $file_offset);
        return self::findBySql($sql_data['sql'], $sql_data['params']);
    }


    /**
     * Counts all uploaded files a user has uploaded.
     *
     * @see FileRef::getUploadedFilesSql for the parameter description.
     *
     * @returns int The amount of files the user uploaded.
     */
    public static function countUploadedFiles($user_id, $begin = null, $end = null, $course_id = '', $unknown_license_only = false)
    {
        $sql_data = self::getUploadedFilesSql($user_id, $begin, $end, $course_id, $unknown_license_only);
        return self::countBySql($sql_data['sql'], $sql_data['params']);
    }
}
