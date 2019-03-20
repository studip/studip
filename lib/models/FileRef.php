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
class FileRef extends SimpleORMap implements PrivacyObject
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
            return $this->owner->nachname . ", " .$this->owner->vorname;
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
     *
     * @return bool True, if the FileRef references a link, false otherwise.
     */
    public function isLink()
    {
        return $this->file->url_access_type === 'redirect';
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
}
