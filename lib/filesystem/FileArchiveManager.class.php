<?php
/**
 * FileArchiveManager.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


/**
 * The FileArchiveManager class gives programmers a simple way to handle
 * file archives by providing different methods for packing and unpacking
 * file archives in a simple manner.
 */
class FileArchiveManager
{

    //ARCHIVE HELPER METHODS

    /**
     * Adds a FileRef to a Zip archive.
     *
     * @param ZipArchive $archive The Zip archive where the FileRef shall be added to.
     * @param FileRef $file_ref The FileRef which shall be added to the zip archive.
     * @param string $user_id The user who wishes to add the FileRef to the archive.
     * @param string $archive_fs_path The path of the file inside the archive's file system.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $ignore_user Set to true, if a file
     *     which has no download restrictions shall be included
     *     and the user-specific download condition check shall be ignored.
     *     If this parameter is set to true, the user_id parameter is irrelevant.
     *     The default for this parameter is false.
     * @return bool True on success, false on failure.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFileRefToArchive(
        ZipArchive $archive,
        FileRef $file_ref,
        $user_id = null,
        $archive_fs_path = '',
        $do_user_permission_checks = true,
        $ignore_user = false,
        &$file_list = null
    )
    {
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1024 * 1024; //1048576 bytes = 1 Mebibyte

        //For FileRef objects we first have to do permission checks
        //using the FileRef's folder object.
        $adding_allowed = false;

        if ($do_user_permission_checks) {
            $folder = $file_ref->getFolderType();
            if (!$folder) {
                return false;
            }

            if ($folder->isReadable($user_id) && $folder->isFileDownloadable($file_ref->id, $user_id)) {
                //FileRef is readable and downloadable for the user (identified by $user_id).
                $adding_allowed = true;
            }
        } elseif ($ignore_user) {
            //we have to check the download condition by looking at the
            //terms of use object of the FileRef:
            if ($file_ref->terms_of_use && $file_ref->terms_of_use->download_condition == 0) {
                $adding_allowed = true;
            }
        } else {
            //Totally skip permission checks:
            $adding_allowed = true;
        }

        if ($adding_allowed) {
            //Adding the FileRef is allowed:
            //Get the file's path (if the file exists) and add the file to the archive:
            $file = $file_ref->file;
            if ($file) {
                //Check if the FileRef references a file
                //in the file system or a link:
                if ($file_ref->file->storage == 'url') {
                    //The FileRef references a link:
                    //Put the URL into a file ending with .url:

                    $url = $file_ref->file->getURL();

                    if ($url) {
                        //The URL has been fetched and we can put it
                        //in a file in the archive:
                        $archive->addFromString(
                            $archive_fs_path . $file_ref->name . '.url',
                            "[InternetShortcut]\nURL={$url}\n"
                        );
                        return true;
                    }
                } else {
                    //The FileRef references a file:
                    $file_path = $file->getPath();
                }
            } else {

                if ($file_ref->path_to_blob) {
                    //The FileRef references a pluginfile:
                    $file_path = $file_ref->path_to_blob;
                }
            }
            if ($file_path && file_exists($file_path)) {
                $archive->addFile($file_path, $archive_fs_path . $file_ref->name);

                //The archive file may not exist if it is empty!
                if (file_exists($archive->filename) && filesize($archive->filename) > $archive_max_size) {
                    throw new FileArchiveManagerException(
                        sprintf(
                            _('Das ZIP-Archiv ist zu groß! Die maximal erlaubte Größe ist %d bytes!'),
                            $archive_max_size
                        )
                    );
                }

                //Add the file to the file list (if available):
                if (is_array($file_list)) {
                    $file_list[] = [
                        'name' => $file_ref->name,
                        'size' => $file_ref->size,
                        'first_name' => isset($file_ref->owner) ? $file_ref->owner->vorname : '',
                        'last_name' => isset($file_ref->owner) ? $file_ref->owner->nachname : '',
                        'downloads' => $file_ref->downloads,
                        'mkdate' => date('d.m.Y H:i', $file_ref->mkdate),
                        'path' => ($archive_fs_path . $file_ref->name)
                    ];
                }

                return true;
            }
        }

        //Something must have gone wrong:
        return false;
    }


    /**
     * Adds a FolderType instance to a Zip archive.
     *
     * @param ZipArchive $archive The Zip archive where the FileRef shall be added to.
     * @param FileRef $file_ref The FileRef which shall be added to the zip archive.
     * @param string $user_id The user who wishes to add the FileRef to the archive.
     * @param string $archive_fs_path The path of the folder inside the archive's file system.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened.
     * @param bool $ignore_user Set to true, if a folder
     *     of type StandardFolder shall be included without checking
     *     if the user (identified by user_id) can read it.
     * @return bool True on success, false on failure.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function addFolderToArchive(
        ZipArchive $archive,
        FolderType $folder,
        $user_id = null,
        $archive_fs_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true,
        $ignore_user = false,
        &$file_list = null
    ) {
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1024 * 1024; //1048576 bytes = 1 Mebibyte

        if ($do_user_permission_checks) {
            //Check if the folder is readable for the user (identified by $user_id):
            if (!$folder->isReadable($user_id)) {
                //Folder is not readable:
                return false;
            }
        } elseif ($ignore_user
                  && !($folder instanceof StandardFolder)
                  && in_array($folder->range_type, ['course', 'institute']))
        {
            //If user permissions shall be skipped the folder must be
            //an instance of StandardFolder and the folder's range type
            //must be course or institute since we can only be sure
            //that StandardFolder instances in courses or institutes
            //are readable by everyone.
            return false;
        }

        $folder_zip_path = $archive_fs_path;
        if ($keep_hierarchy) {
            $folder_zip_path .= $folder->name;
            $archive->addEmptyDir($folder_zip_path);
        }
        foreach ($folder->getFiles() as $file_ref) {

            if (!$file_ref instanceof FileRef) {
                $plugin = PluginManager::getInstance()->getPlugin($folder->range_id);
                if (!$plugin) {
                    $plugin = PluginManager::getInstance()->getPlugin($folder->range_type);;
                }
                if ($plugin) {
                    $file_ref = $plugin->getPreparedFile($file_ref->id, true);
                }
            }

            self::addFileRefToArchive(
                $archive,
                $file_ref,
                $user_id,
                //keep hierarchy in zip file (files and subdirectories)
                $keep_hierarchy ? $folder_zip_path . '/' : '',
                $do_user_permission_checks,
                $ignore_user,
                $file_list
            );
        }

        foreach ($folder->getSubfolders() as $subfolder) {
            self::addFolderToArchive(
                $archive,
                $subfolder,
                $user_id,
                //keep hierarchy in zip file (files and subdirectories)
                $keep_hierarchy ? $folder_zip_path . '/' : '',
                $do_user_permission_checks,
                $keep_hierarchy,
                $ignore_user,
                $file_list
            );
        }

        if (file_exists($archive->filename) && filesize($archive->filename) > $archive_max_size) {
            throw new FileArchiveManagerException(
                sprintf(
                    _('Das ZIP-Archiv ist zu groß! Die maximal erlaubte Größe ist %d bytes!'),
                    $archive_max_size
                )
            );
        }

        return true;
    }


    //ARCHIVE CREATION METHODS


    /**
     * General method for creating file archives.
     *
     * This method is a generalisation for all archive creation methods.
     * For easier archive creation you may use the other archive creation
     * methods which work with less arguments.
     *
     * @param Array $file_area_objects Array of FileRef, FileURL, Folder or FolderType objects.
     *     $file_area_objects may contain a mix between those object types.
     * @param string $user_id The user who wishes to pack files.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if individual
     *     reading/downloading permissions shall be checked. False otherwise.
     *     Default is true.
     * @param bool $keep_hierarchy True, if the folder hierarchy shall be kept.
     *     False, if the folder hierarchy shall be flattened. Default is true.
     * @param bool $ignore_user Set to true, if all files
     *     which have no download restrictions and all folders which are of type
     *     StandardFolder shall be included and the user-specific
     *     download condition check shall be ignored.
     *     If this parameter is set to true, the user_id parameter is irrelevant.
     *     The default for this parameter is false.
     * @param string $zip_encoding encoding for filenames in zip
     * @param bool $add_filelist_to_archive If this is set to true a file list
     *     in the CSV format will be added to the archive. Its name is hardcoded
     *     to archive_filelist.csv. The default value of $add_filelist_to_archive
     *     is false which means no file list is added.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs a general exception or a more
     *     special exception is thrown.
     */
    public static function createArchive(
        $file_area_objects = [],
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true,
        $ignore_user = false,
        $zip_encoding = 'UTF-8',
        $add_filelist_to_archive = false
    )
    {
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size =  Config::get()->ZIP_DOWNLOAD_MAX_SIZE * 1024 * 1024; //1048576 bytes = 1 Mebibyte

        // check if archive path is set:
        if (!$archive_file_path) {
            throw new FileArchiveManagerException(
                _('Der Zielpfad für das Archiv wurde nicht angegeben!')
            );
        }

        // $file_area_objects must be a non-empty array!
        // Otherwise we would return an empty Zip archive.
        if (!is_array($file_area_objects) || empty($file_area_objects)) {
            return false;
        }

        // We can create the Zip archive now since its path exists in the file system
        // and furthermore there are file area objects available.
        $archive = new Studip\ZipArchive();
        if (!$archive->open($archive_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new FileArchiveManagerException('Error opening new ZIP archive!');
        }
        $archive->setOutputEncoding($zip_encoding);
        //Check if more file area objects than allowed shall be packed:
        //In that case, stop here.
        if ($archive_max_num_files && count($file_area_objects) > $archive_max_num_files) {
            throw new FileArchiveManagerException(
                sprintf(
                    _('Das Archiv beinhaltet zu viele Dateibereich-Objekte! Die Obergrenze liegt bei %d Objekten!'),
                    $archive_max_num_files
                )
            );
        }

        //If $file_list is not an array
        //then no files are added to the file list.
        $file_list = null;
        if ($add_filelist_to_archive) {
            $file_list = [];
        }

        foreach ($file_area_objects as $file_area_object) {
            if ($file_area_object instanceof FileRef) {
                self::addFileRefToArchive(
                    $archive,
                    $file_area_object,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $ignore_user,
                    $file_list
                );
            } elseif ($file_area_object instanceof Folder || $file_area_object instanceof FolderType) {
                $folder = $file_area_object;
                if ($folder instanceof Folder) {
                    //We use FolderType instances here.
                    $folder = $folder->getTypedFolder();
                }

                self::addFolderToArchive(
                    $archive,
                    $folder,
                    $user_id,
                    '',
                    $do_user_permission_checks,
                    $keep_hierarchy,
                    $ignore_user,
                    $file_list
                );
            }
        }

        if ($archive->numFiles > 0) {
            //At least one file is in the archive.

            if ($add_filelist_to_archive) {
                //If a file list shall be included in the ZIP archive
                //we must now make a CSV file out of file_list:

                $csv_data = array_merge(
                    [
                        [
                            _('Name'),
                            _('Größe'),
                            _('Vorname'),
                            _('Nachname'),
                            _('Downloads'),
                            _('Datum'),
                            _('Pfad')
                        ]
                    ],
                    $file_list
                );

                //The CSV file has been generated.
                //Now we must add it to the archive:
                $archive->addFromString('archive_filelist.csv', array_to_csv($csv_data));
            }

            //Now the ZIP file is really finished:
            return $archive->close();
        }

        //empty archive
        return false;
    }

    /**
     * Puts files (identified by their file refs) into one file archive.
     *
     * @param FileRef[] $file_refs Array of FileRef objects.
     * @param User $user The user who wishes to pack files.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading
     *     permissions shall be checked. False otherwise. Default is true.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFileRefs(
        $file_refs,
        User $user,
        $archive_file_path = '',
        $do_user_permission_checks = true
    )
    {
        $archive_max_num_files = Config::get()->ZIP_DOWNLOAD_MAX_FILES;
        $archive_max_size = Config::get()->ZIP_DOWNLOAD_MAX_SIZE;

        if (!$archive_file_path) {
            throw new FileArchiveManagerException(
                _('Der Zielpfad für das Archiv wurde nicht angegeben!')
            );
        }

        //We must now collect all the files from these FileRefs and copy them
        //into the new archive file.

        return self::createArchive(
            $file_refs,
            $user->id,
            $archive_file_path,
            $do_user_permission_checks,
            false //do not keep the file hierarchy
        );
    }

    /**
     * Returns all children of a folder type.
     *
     * @param FolderType $folder
     * @return array
     */
    private static function getFolderChildren(FolderType $folder)
    {
        $children = [];
        foreach ($folder->subfolders as $folder) {
            $children[] = $folder;
        }
        foreach ($folder->file_refs as $ref) {
            $children[] = $ref;
        }
        return $children;
    }

    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     *
     * @param FolderType $folder The folder whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromFolder(
        FolderType $folder,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true
    )
    {
        return self::createArchive(
            self::getFolderChildren($folder),
            $user_id,
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }

    /**
     * Creates an archive that contains all files of a course the given user
     * is allowed to download.
     *
     * @param string $course_id The ID of the course whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the course's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromCourse(
        $course_id,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true
    )
    {
        $folder = Folder::findTopFolder($course_id);
        if (!$folder) {
            return null;
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            return null;
        }

        return self::createArchive(
            self::getFolderChildren($folder),
            $user_id,
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }


    /**
     * Creates an archive that contains all files of an institute the given user
     * is allowed to download.
     *
     * @param string $institute_id The ID of the institute whose files shall be put inside an archive.
     * @param string $user_id The ID of the user who wishes to put the institute's files into an archive
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromInstitute(
        $institute_id,
        $user_id = null,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true)
    {
        $folder = Folder::findTopFolder($institute_id);
        if(!$folder) {
            return null;
        }

        $folder = $folder->getTypedFolder();
        if(!$folder) {
            return null;
        }

        return self::createArchive(
            self::getFolderChildren($folder),
            $archive_file_path,
            $archive_file_name,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }

    /**
     * Creates an archive that contains all files of a user, if the current
     * user has root permissions to do this.
     *
     * @param string $user_id The ID of the user whose files shall be put inside an archive.
     * @param string $archive_file_path The path for the archive file.
     * @param bool $do_user_permission_checks Set to true if reading/downloading permissions
     *     shall be checked. False otherwise. Default is true.
     * @param bool $keep_hierarchy True, if the file hierarchy shall be kept inside the archive.
     *     If $keep_hierarchy is set to false you will get an archive that contains only files
     *     and no subdirectories.
     *
     * @return bool True, if the archive file was created and saved successfully
     *     at $archive_file_path, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromUser(
        $user_id,
        $archive_file_path = '',
        $do_user_permission_checks = true,
        $keep_hierarchy = true
    )
    {
        $folder = Folder::findTopFolder($user_id);
        if (!$folder) {
            return null;
        }

        $folder = $folder->getTypedFolder();
        if (!$folder) {
            return null;
        }

        return self::createArchive(
            self::getFolderChildren($folder),
            $archive_file_path,
            $do_user_permission_checks,
            $keep_hierarchy
        );
    }


    /**
     * This method creates an archive with the content of a physical folder
     * (A folder inside the operating system's file system).
     *
     * @param string $folder_path The path to the physical folder
     *     which content shall be added to a file archive.
     * @param string $archive_file_path The path to the archive file which
     *     shall be created.
     *
     * @return True, if all files were added successfully, false otherwise.
     *
     * @throws Exception|FileArchiveManagerException If an error occurs
     *     a general exception or a more special exception is thrown.
     */
    public static function createArchiveFromPhysicalFolder($folder_path, $archive_file_path)
    {
        if (!$folder_path || !$archive_file_path) {
            //we can't work with empty paths!
            return false;
        }

        if (!file_exists($folder_path)) {
            //path to physical folder does not exist!
            throw new FileArchiveManagerException(
                _('Der Ordner wurde im Dateisystem des Servers nicht gefunden!')
            );
        }

        //Put all the content of the folder inside an archive:
        $archive = Studip\ZipArchive::create($archive_file_path);
        $result = $archive->addFromPath($folder_path);
        $archive->close();
        return $result;
    }

    //ARCHIVE EXTRACTION METHODS

    /**
     * This is a helper method that builds a subfolder hierarchy inside
     * a folder by looking at a string representing a file system path.
     *
     * The variable $path contains a hierarchy of subfolders that shall be created
     * inside the given folder. If $path contains "folder1/folder2/folder3" then
     * the given folder will get a subfolder named "folder1". The folder
     * "folder1" itself will get a subfolder named "folder2" and so on.
     *
     * @param FolderType $folder The folder where a subfolder path shall be created.
     * @param User $user The user who wishes to create the path.
     * @param string $path The path which shall be created inside $folder.
     *
     * @return FolderType[] An array with FolderType objects representing
     *     each element of $path.
     */
    public static function createFolderPath(FolderType $folder, User $user, $path = '')
    {
        if (!$path) {
            return [];
        }

        // now we strip leading and trailing slashes, whitespaces and other characters:
        // then we convert path into an array of strings:
        $path = trim($path, ' /');
        $path = explode('/', $path);

        //now we loop through path and build subfolders:
        $folder_path = [];

        $current_folder = $folder;
        foreach ($path as $new_folder_name) {
            //first we check if the folder already exists:
            foreach ($current_folder->getSubfolders() as $subfolder) {
                if ($subfolder->name === $new_folder_name) {
                    //We have found a folder that has the name $new_folder_name:
                    //No need to create a new folder, we can use that folder
                    //and continue with it:
                    $current_folder = $subfolder;
                    $folder_path[] = $subfolder;

                    //start next iteration of the outer foreach loop:
                    continue 2;
                }
            }

            //If code execution has reached this point we have looped
            //throug all subfolders of the current folder and couldn't find
            //any subfolder that matches the name given in $new_folder_name.
            //Therefore we must create a new folder here, if possible:

            //Check the user's permissions first:
            if ($current_folder->isSubfolderAllowed($user->id)) {
                //Create a subfolder:
                $result = FileManager::createSubFolder(
                    $current_folder,
                    $user,
                    get_class($current_folder) === RootFolder::class ? StandardFolder::class : get_class($current_folder),
                    $new_folder_name
                );

                if ($result instanceof FolderType) {
                    $folder_path[] = $result;
                }
            }
        }
        return $folder_path;
    }

    /**
     * Extracts one file from an opened archive and stores it in a folder.
     *
     * @param ZipArchive $archive The archive from which a file shall be extracted.
     * @param string $archive_path The path of the file in the archive.
     * @param FolderType $target_folder The folder where the file shall be stored.
     * @param User $user The user who wishes to extract the file from the archive.
     *
     * @return FileRef|null FileRef instance on success, null otherwise.
     */
    public static function extractFileFromArchive(
        Studip\ZipArchive $archive,
        $archive_path,
        FolderType $target_folder,
        User $user
    )
    {
        $file_resource = $archive->getStream($archive_path);
        $file_info     = $archive->statName($archive_path);

        if (!$file_resource) {
            return null;
        }

        $file = new File();
        $file->user_id   = $user->id;
        $file->name      = $archive->convertArchiveFilename(basename($archive_path));
        $file->mime_type = get_mime_type($file->name);
        $file->size      = $file_info['size'];
        $file->storage   = 'disk';
        $file->store();

        // Ok, we have a file object in the database. Now we must connect
        // it with the data file by extracting the data file into
        // the place, where the file's content has to be placed.
        $file_path = pathinfo($file->getPath(), PATHINFO_DIRNAME);

        // Create the directory for the file, if necessary:
        if (!is_dir($file_path)) {
            mkdir($file_path);
        }

        // Ok, now we read all data from $file_resource and put it into
        // the file's path:
        if (file_put_contents($file->getPath(), $file_resource) === false) {
            //Something went wrong: abort and clean up!
            $file->delete();
            return null;
        }

        // Ok, we now must create a FileRef:
        $file_ref = new FileRef();
        $file_ref->file_id   = $file->id;
        $file_ref->folder_id = $target_folder->getId();
        $file_ref->user_id   = $user->id;
        $file_ref->name     = $file->name;
        if($file_ref->store()) {
            return $file_ref;
        }

        //Something went wrong: abort and clean up!
        $file_ref->delete();
        return null;
    }

    /**
     * Extracts an archive into a folder inside the Stud.IP file area.
     *
     * @param FileRef $archive_file_ref The archive file which shall be extracted.
     * @param FolderType $folder The folder where the archive shall be extracted.
     * @param string $user_id The ID of the user who wants to extract the archive.
     *
     * @return FileRef[] Array with extracted files, represented as FileRef objects.
     */
    public static function extractArchiveFileToFolder(
        FileRef $archive_file_ref,
        FolderType $folder,
        $user_id = null
    )
    {
        $user = $user_id ? User::find($user_id) : User::findCurrent();
        if (!$user) {
            return [];
        }

        // Determine, if the folder is writable for the user identified by $user_id:
        if (!$folder->isWritable($user->id)) {
            return [];
        }

        // Determine if we can keep the zip archive's folder hierarchy:
        $keep_hierarchy = $folder->isSubfolderAllowed($user->id);

        $archive = new Studip\ZipArchive();
        $archive->open($archive_file_ref->file->getPath());

        // loop over all entries in the zip archive and put each entry
        // in the current folder or one of its subfolders:
        $file_refs = [];

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $entry_info = $archive->statIndex($i);
            $entry_info_name = $archive->convertArchiveFilename($entry_info['name']);
            // split the entry's path into its path and its name component:
            $entry_path = ltrim(pathinfo($entry_info_name, PATHINFO_DIRNAME), '.');
            $entry_name = pathinfo($entry_info_name, PATHINFO_BASENAME);

            // check if $entry_info['name'] ends with a slash:
            // In that case it is a directory entry:
            $entry_is_directory = preg_match('/\/$/', $entry_info_name);

            //The folder where the extracted file/folder shall be inserted:
            $extracted_entry_destination_folder = $folder;

            if ($keep_hierarchy) {
                //Keep the archive's folder hierarchy:
                //We may have to create subfolders.
                if (basename($entry_path)) {
                    //The file/folder doesn't lie in the "top folder" of the archive:
                    //Pass the path to createFolderPath and let it generate
                    //a folder path before extracting the file:
                    $folder_path = self::createFolderPath(
                        $folder,
                        $user,
                        $entry_path
                    );

                    //Get the last element of $folder_path:
                    $last_folder_path_element = array_pop($folder_path);

                    //Compare $extracted_entry_destination_folder's name with the name of the
                    //last path item in $file_archive_path. Only if they are equal
                    //we can use that folder to store the file. Otherwise
                    //we must continue with the next file entry in the archive:
                    if ($last_folder_path_element
                        && $last_folder_path_element->name === basename($entry_path))
                    {
                        $extracted_entry_destination_folder = $last_folder_path_element;
                    }
                }
            }

            if ($entry_is_directory) {
                //We have to create a subfolder if it doesn't exist yet:
                self::createFolderPath(
                    $extracted_entry_destination_folder,
                    $user,
                    $entry_name
                );
            } else {
                //we extract one file:
                //$entry_info['name'] is necessary because we need the full path
                //to the entry inside the archive.
                $file_ref = self::extractFileFromArchive(
                    $archive,
                    $entry_info['name'],
                    $extracted_entry_destination_folder,
                    $user
                );

                if ($file_ref instanceof FileRef) {
                    $file_refs[] = $file_ref;
                }
            }
        }

        return $file_refs;
    }
}
