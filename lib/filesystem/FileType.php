<?php

interface FileType
{
    /**
     * Returns the name of the icon shape that shall be used with the FileType implementation.
     *
     * @param string $role role of icon
     * @return Icon icon for the FileType implementation.
     */
    public function getIcon($role);

    /**
     * Returns the id of the file which is most likely the id of the FileRef object
     * within the FileType object.
     * @return mixed
     */
    public function getId();

    /**
     * Filename of the FileType-object.
     * @return mixed
     */
    public function getFilename();

    /**
     * The user_id in Stud.IP if the author has Stud.IP account. If it has none, return null.
     * @return mixed|null
     */
    public function getUserId();

    /**
     * Return the name of the author as a string.
     * @return string|null
     */
    public function getUserName();


    /**
     * @returns The User object representing the author.
     */
    public function getUser();


    /**
     * Returns the size of the file in bytes. If this is null, the file doesn't exist
     * physically - is probably only a weblink or a request for libraries.
     * @return integer|null
     */
    public function getSize();

    /**
     * Returns the URL to download the file. May be sendfile.php?... or an external link.
     * @return string|null
     */
    public function getDownloadURL();

    /**
     * Returns the number of downloads this file already has. Returns null if information is not available.
     * @return integer|null
     */
    public function getDownloads();


    /**
     * Returns the (real) file system path for the file.
     * This is only relevant for FileType implementations storing real files
     * on the server disk. Other implementations shall just return
     * an empty string.
     *
     * @returns The file system path for the file or an empty string if the
     *     file doesn't have a path in the file system.
     */
    public function getPath() : string;

    /**
     * Returns the UNIX-Timestamp of the last change or null if this information is unknown.
     * @return integer|null
     */
    public function getLastChangeDate();

    /**
     * Returns the UNIX-timestamp of creation of that file
     * @return integer|null
     */
    public function getMakeDate();

    /**
     * Returns the description of that FileType object.
     * @return string|null
     */
    public function getDescription();

    /**
     * Returns the mime-type of that FileType-object.
     * @return string
     */
    public function getMimeType();

    /**
     * @return ContentTermsOfUse
     */
    public function getTermsOfUse();

    /**
     * Returns an instance of ActionMenu.
     * @return ActionMenu|null
     */
    public function getActionmenu();


    /**
     * Returns a list of Stud.IP button objects that represent actions
     * that shall be visible for the file type in the info dialog.
     *
     * @param array $extra_link_params An optional array of URL parameters
     *     that should be added to Button URLs, if reasonable. The parameter
     *     names are the keys of the array while their values are also the
     *     array item values.
     *
     * @returns Interactable[] A list of Stud.IP buttons (LinkButton or Button).
     */
    public function getInfoDialogButtons(array $extra_link_params = []) : array;


    /**
     * Deletes that file.
     * @return bool : true on success
     */
    public function delete();

    /**
     * Returns the FolderTyp of the parent folder.
     * @return FolderType
     */
    public function getFolderType();

    /**
     * Determines whether the file is visible for a user.
     *
     * @param string $user_id The user for which the visibility of the file
     *     shall be determined.
     *
     * @return boolean True, if the user is permitted to see the file, false otherwise.
     */
    public function isVisible($user_id = null);

    /**
     * Determines if a user may download the file.
     * @param string $user_id The user who wishes to download the file.
     * @return boolean True, if the user is permitted to download the file, false otherwise.
     */
    public function isDownloadable($user_id = null);

    /**
     * Determines if a user may edit the file.
     * @param string $user_id The user who wishes to edit the file.
     * @return boolean True, if the user is permitted to edit the file, false otherwise.
     */
    public function isEditable($user_id = null);

    /**
     * Determines if a user may write to the file.
     * @param string $user_id The user who wishes to write to the file.
     * @return boolean True, if the user is permitted to write to the file, false otherwise.
     */
    public function isWritable($user_id = null);

    /**
     * Returns an object of the class StandardFile or a derived class.
     * @return StandardFile
     */
    public function convertToStandardFile();

    /**
     * Returns the content for that additional column, if it exists. You can return null a string
     * or a Flexi_Template as the content.
     * @param string $column_index
     * @return null|string|Flexi_Template
     */
    public function getContentForAdditionalColumn($column_index);

    /**
     * Returns an integer that marks the value the content of the given column should be
     * ordered by.
     * @param string $column_index
     * @return integer : order value
     */
    public function getAdditionalColumnOrderWeigh($column_index);


    /**
     * Generates a Flexi_Template containing additional information that are
     * displayes in the information dialog of a file.
     *
     * @param bool $include_downloadable_infos Whether to include information
     *     like file previews that can be downloaded (true) or to not
     *     include them (false). Defaults to false.
     *
     * @returns Flexi_Template|null Either a Flexi_Template containing
     *     additional information or null if no such information shall be
     *     displayed in the information dialog.
     */
    public function getInfoTemplate(bool $include_downloadable_infos = false);
}
