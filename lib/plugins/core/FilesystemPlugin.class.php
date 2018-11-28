<?php
interface FilesystemPlugin
{
    /**
     * Returns a Navigation-object. Only the title and the image will be used.
     *
     * @return null|Navigation with title and image
     */
    public function getFileSelectNavigation();

    /**
     * Returns an URL to a page, where the filesystem can be configured.
     *
     * @return mixed
     */
    public function filesystemConfigurationURL();

    /**
     * Determines if this filesystem plugin should be a source for copying or a search.
     * This may be dependend on the current user and his/her configurations.
     *
     * @return boolean
     */
    public function isSource();

    /**
     * Determines if this filesystem-plugin should show up as a personal file-area and be a destination
     * for copied files.
     * This may be dependend on the current user and his/her configurations.
     *
     * @return boolean
     */
    public function isPersonalFileArea();

    /**
     * This method is used to get a folder-object for this plugin.
     * Not recommended but still possible is to return a Flexi_Template for the folder, if you want to
     * take care of the frontend of displaying the folder as well.
     *
     * @param null $folder_id : folder_id of folder to get or null if you want the top-folder
     * @return FolderType|Flexi_Template
     */
    public function getFolder($folder_id = null);

    /**
     * @param $file_id : The id for the file in the given filesystem of the plugin.
     * @return array : the already prepared File just like a file-upload-array
     */
    public function getPreparedFile($file_id, $with_blob = false);

    /**
     * Defines if the filesystem-plugin has a search-function.
     *
     * @return mixed
     */
    public function hasSearch();

    /**
     * Returns an array for each special search parameter. Each parameter is itself represented by as associative array
     * like
     *     array(
     *         'name' => "name of this parameter in the form",
     *         'type' => "one of 'text', 'checkbox', 'select'",
     *         'options' => array() //only neccesary if type is 'select' - a key-value array with the key key as the value of the select and the value as the label of the option
     *         'placeholder' => "only possible for type 'text' but not mandatory"
     *     )
     * This method can also return an empty array or null if no search parameters are needed or no search is provided at all.
     *
     * @return null|array(array(), ...)
     */
    public function getSearchParameters();

    /**
     * Returns a virtual folder that 'contains' all the files as a search-result. Only return null
     * if search is not implemented.
     *
     * @param string $text a string
     * @param array $parameters : an associative array of additional search parameters as defined in getSearchParameters()
     * @return FolderType|null
     */
    public function search($text, $parameters = []);
}
