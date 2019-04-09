<?php
/**
 * Folder.php
 * model class for table folders
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
 * @property string id database column
 * @property string user_id database column: owner of folder
 * @property string parent_id database column: parent folder
 * @property string range_id database column: user-ID, course-ID, institute-ID etc.
 * @property string range_type database column: 'course', 'institute', 'user', ...
 * @property string folder_type database column
 * @property string name database column: folder name
 * @property string data_content database column
 * @property string description database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class Folder extends SimpleORMap
{
    /**
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'folders';
        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        ];
        $config['has_many']['file_refs'] = [
            'class_name'        => 'FileRef',
            'assoc_foreign_key' => 'folder_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY name ASC'
        ];
        $config['has_many']['subfolders'] = [
            'class_name'        => 'Folder',
            'assoc_foreign_key' => 'parent_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY name ASC'
        ];
        $config['belongs_to']['parentfolder'] = [
            'class_name'  => 'Folder',
            'foreign_key' => 'parent_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['institute'] = [
            'class_name'  => 'Institute',
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['message'] = [
            'class_name'  => 'Message',
            'foreign_key' => 'range_id',
        ];
        $config['serialized_fields']['data_content'] = 'JSONArrayObject';

        $config['registered_callbacks']['before_store'][] = 'cbMakeUniqueName';

        parent::configure($config);
    }

    /**
     * Creates a top folder (root directory) for a Stud.IP object given by range_id and range_type.
     *
     * This method creates and stores a top folder (root directory) for a Stud.IP object.
     * To properly create such a folder this method requires the parameters
     * range_id and range_type to be set.
     *
     * @param string $range_id The ID of the Stud.IP object
     * @param string $range_type The type of the object: "course", "inst", "user", ...
     *
     * @return Folder Created Folder object.
     */
    public static function createTopFolder($range_id, $range_type, $folder_type = 'RootFolder')
    {
        return self::create([
            'parent_id'    => '',
            'range_id'     => $range_id,
            'range_type'   => $range_type,
            'description'  => '',
            'name'         => '',
            'data_content' => '',
            'folder_type'  => $folder_type,
            'user_id'      => $GLOBALS['user']->id
        ]);
    }

    /**
     * Determines the range type by probing the given range ID.
     *
     * This is a helper method that can be used in conjunction with the
     * createTopFolder method. In case when only the ID of a Stud.IP object
     * is given, this method will help to determine the corresponding
     * object type.
     *
     * @param string $range_id The ID of an object whose type shall be determined.
     *
     * @return bool|string Returns false on failure, otherwise the name of the range.
     */
    public static function findRangeTypeById($range_id)
    {
        //If range_id isn't set we don't need to query the database at all!
        //Therefore we check first, if range_id validates to false.
        if (!$range_id) {
            return false;
        }

        if (Course::exists($range_id)) {
            return 'course';
        }
        if (Institute::exists($range_id)) {
            return 'institute';
        }
        if (User::exists($range_id)) {
            return 'user';
        }
        if (Message::exists($range_id)) {
            return 'message';
        }

        return false;
    }

    /**
     * Retrieves folders by the ID of a CourseTopic object.
     *
     * @param string $topic_id The ID of a CourseTopic object.
     *
     * @return Folder[] An array with one folder object matching the topic-ID
     *     if such a folder can be found. Null otherwise.
     */
    public static function findByTopic_id($topic_id)
    {
        $seminar_id    = CourseTopic::find($topic_id)->seminar_id;
        $topic_folders = self::findBySQL(
            "folder_type = 'CourseTopicFolder' AND range_id = ? AND range_type = 'course'",
            [$seminar_id]
        );
        $ret = [];
        foreach ($topic_folders as $key => $folder) {
            if ($folder['data_content']['topic_id'] === $topic_id) {
                $ret[] = $folder;
            }
        }
        return $ret;
    }

    /**
     * Retrieves folders by the ID of a CourseDate object.
     *
     * @param string $termin_id The ID of a CourseDate object.
     *
     * @return Folder[] An array with one folder object matching the $termin_id
     *     if such a folder can be found. Null otherwise.
     */
    public static function findByTermin_id($termin_id)
    {
        $seminar_id   = CourseDate::find($termin_id)->range_id;
        $date_folders = self::findBySQL(
            "folder_type = 'CourseDateFolder' AND range_id = ? AND range_type = 'course'",
            [$seminar_id]
        );
        $ret = [];
        foreach ($date_folders as $key => $folder) {
            if ($folder['data_content']['termin_id'] === $termin_id) {
                $ret[] = $folder;
            }
        }
        return $ret;
    }

    /**
     * This callback is called before storing a Folder object.
     * In case the name field is changed this callback assures that the
     * name of the Folder object is unique inside the parent folder.
     */
    public function cbMakeUniqueName()
    {
        //If this folder has a parent folder we must check if either the
        //name or the parent_id field has changed recently.
        //If one of those fields has changed we must request an unique name
        //to avoid duplicate folder names.
        if (isset($this->parentfolder) &&
            ($this->isFieldDirty('name') || $this->isFieldDirty('parent_id'))) {
            $this->name = $this->parentfolder->getUniqueName($this->name, true);
        }
    }

    /**
     * Checks if a file or folder with a given file name exists inside the folder.
     *
     * By looking at the number of associated FileRef objects and
     * the number of associated Folder objects this method determines
     * if a file or folder with a given name exists inside the folder.
     *
     * @param string $file_name The file name of the file or folder which is searched.
     *
     * @return bool Returns true, if a file was found, false otherwise.
     **/
    public function fileExists($file_name)
    {
        //get files :
        $found_files = FileRef::countBySql("name = :file_name AND folder_id = :id", [
            'file_name' => $file_name,
            'id'        => $this->id,
        ]);

        $found_folders = Folder::countBySql("name = :file_name AND parent_id= :id",[
            'file_name' => $file_name,
            'id'        => $this->id,
        ]);

        return $found_files + $found_folders > 0;
    }

    /**
     * Makes a given file name unique and returns the altered file name.
     *
     * The file and folder names in a folder must be unique. This helper method
     * will check, if a file or folder with the name given by the parameter
     * $file_name exists and if so, it will append a number in square brackets
     * to the file name to make it unique. The unique file name is returned.
     *
     * @param string $file_name The file name that shall be checked for uniqueness.
     * @param bool $folder_naming_mode Enable (true) or disable (false) the
     *     Folder naming mode. The default is false (disabled).
     *     If folder naming mode is set to true, the number in square brackets
     *     is appended to the end of the name instead of being inserted before
     *     the last dot in the name.
     *
     * @return string An unique filename.
     */
    public function getUniqueName($file_name, $folder_naming_mode = false)
    {
        $c = 0;
        if ($folder_naming_mode) {
            //Folder naming mode: simply append the number in square brackets.
            $original_name = $file_name;
            while ($this->fileExists($file_name)) {
                $file_name = $original_name . '[' . ++$c . ']';
            }
        } else {
            //File naming mode: The last dot in the name is important.
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($file_name, 0, -mb_strlen('.' . $ext));
            } else {
                $name = $file_name;
            }
            while ($this->fileExists($file_name)) {
                $file_name = $name . '[' . ++$c . ']';
                if ($ext) {
                    $file_name .= '.' . $ext;
                }
            }
        }
        return $file_name;
    }

    /**
     * Find the top folder of a Stud.IP object or create it, if it doesn't exist.
     *
     * This method finds the top folder (root directory) of a course, institute,
     * personal file area or a message by the ID given in the range_id parameter.
     * If the root folder doesn't exist, it will be created.
     *
     * Note that the range_id parameter is mandatory!
     *
     * @param string range_id The ID of the Stud.IP object whose top folder shall be found.
     *
     * @returns Folder|null Folder object on success or null, if no folder can be created.
     **/
    public static function findTopFolder($range_id)
    {
        $top_folder = self::findOneBySQL(
            "range_id = ? AND parent_id=''",
            [$range_id]
        );

        //top_folder may not exist!
        if (!$top_folder) {
            //top_folder doest not exist: create it
            //determine range type:
            $range_type = self::findRangeTypeById($range_id);
            if (!$range_type) {
                //no range type means we can't create a folder!
                return null;
            }

            $top_folder = self::createTopFolder($range_id, $range_type);
        }

        return $top_folder;
    }

    /**
     * Gets the FolderType object for the current folder.
     *
     * The FolderType class defines extended attributes for a folder.
     * With this method the associated FolderType of a folder can be
     * determined.
     *
     * @return FolderType An object of a FolderType derivate.
     * @throws UnexpectedValueException If the class specified by the folder's folder_type attribute can't be found an Exception is thrown.
     */
    public function getTypedFolder()
    {
        if (!class_exists($this->folder_type)) {
            return new UnknownFolderType($this);
        }

        if (!is_subclass_of($this->folder_type, 'FolderType')) {
            throw new UnexpectedValueException(sprintf(
                'Class %s (from folder %s) does not implement the FolderType interface!',
                $this->folder_type,
                $this->id
            ));
        }

        return new $this->folder_type($this);
    }

    /**
     * Creates a FileRef object for a given File object or its ID.
     *
     * This method creates a FileRef object for a file that is represented
     * by its object or its ID. The new FileRef's description is different
     * than the one from the file since it is set via the $description parameter.
     * Furthermore license information can be stored via the $license parameter.
     *
     * @param File|string $file_or_id Either a file object or a string containing a File object's ID.
     * @param array $file_ref_data The description for the file that shall be used in the FileRef object.
     * @return FileRef|null On success a FileRef for the given file is returned. On failure, null is returned.
     */
    public function linkFile($file_or_id, $file_ref_data = [])
    {
        $file = File::toObject($file_or_id);
        if (!$file) {
            //file object wasn't found!
            return null;
        }

        $ref = new FileRef();
        $ref->name    = $file->name;
        $ref->user_id = $file->user_id;

        $ref->setData($file_ref_data);

        $ref->file_id = $file->id;
        $ref->folder  = $this;
        if ($ref->store()) {
            return $ref;
        }

        return null;
    }

    /**
     * Removes a file reference.
     * @param FileRef|string $fileref_or_id The FileRef itself or its ID
     * @return int|bool Returns the amount of deleted databasw rows on success or false on failure.
     */
    public function unlinkFileRef($fileref_or_id)
    {
        $fileref = FileRef::toObject($fileref_or_id);
        return $fileref->delete();
    }

    /**
     * Returns a list of parent folders, starting with the top folder.
     *
     * This method returns a list with the parent folders of the folder
     * until the top folder (root directory) is found.
     * The list is reversed so that it starts with the top folder and
     * ends with this folder.
     *
     *
     * @return \Folder[] An array of parent folders, starting with the top folder.
     */
    public function getParents()
    {
        $path = [];
        $current = $this;
        while ($current) {
            $path[] = $current;
            if (!$current->parent_id) {
                break;
            }
            $current = $current->parentfolder;
        }
        $path = array_reverse($path);
        return $path;
    }


    /**
     * Returns the file system path from the top folder to this folder.
     *
     * By calling the getParents method of this class and getting the names
     * of the parent folders the path is created. The default path separator
     * is a slash, but it can be overwritten by specifying the $delimiter parameter.
     *
     * @param string $delimiter The character to be used as path separator.
     * @return string The path from the top folder to this folder, separated by the character set in $delimiter.
     */
    public function getPath($delimiter = '/')
    {
        $parents = $this->getParents();
        return join($delimiter, SimpleCollection::createFromArray($parents)->pluck('name'));
    }
}
