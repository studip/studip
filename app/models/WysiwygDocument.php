<?php
/**
 * wysiwygdocument.php - Manage files uploaded by the WYSIWYG editor.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
namespace Studip;

/**
 * Info about Stud.IP documents uploaded by the WYSIWYG editor and collection
 * of static methods for uploading files.
 */
class WysiwygDocument
{
    /**
     * Construct new WysiwygDocument for an uploaded Stud.IP document.
     */
    public function __construct($studipDocument, $mimeType)
    {
        $this->studipDocument = $studipDocument;
        $this->mimeType = $mimeType;
    }

    /**
     * @return string  The actual file's name without its path.
     */
    public function filename()
    {
        return $this->studipDocument['filename'];
    }

    /**
     * @return string  Mime-type of the uploaded document.
     */
    public function type()
    {
        return $this->mimeType;
    }

    /**
     * Return URL for downloading the file.
     *
     * @param  string $id  File identifier in database table 'dokumente'.
     * @return string      Download link, NULL if file doesn't exist.
     */
    public function url()
    {
        return \GetDownloadLink($this->studipDocument->getId(),
                                $this->filename());
    }

    //// file upload //////////////////////////////////////////////////////////

    /**
     * Store uploaded files as StudIP documents.
     * @param FolderType $foldertype
     * @return array  Associative array containing upload results.
     */
    public static function storeUploadedFilesIn($foldertype)
    {
        $results = array();  // data for HTTP response
        foreach (self::getUploadedFiles() as $file) {
            try {
                $fileref = $foldertype->createFile($file);
                //$document = self::fromUpload($file, $folder_id);
                $results['files'][] = Array(
                    'name' => $fileref->filename,
                    'type' => $fileref->mime_type,
                    'url' => $fileref->getDownloadURL()
                );
            } catch (\AccessDeniedException $e) { // document creation failed
                $results['files'][] = Array(
                    'name' => $file['name'],
                    'type' => $file['type'],
                    'error' => $e->getMessage()
                );
            }
        }
        return $results;
    }

    /**
     * Normalize $_FILES for HTML array upload of multiple files.
     *
     * $_FILES must have the following structure (HTML array upload):
     *
     * ['files' => ['name'     => [name1, name2, ...],
     *              'tmp_name' => [tmp1, tmp2, ...],
     *              'type'     => [type1, type2, ...],
     *              'size'     => [size1, size2, ...],
     *              'error'    => [error1, error2, ...],
     *              ...]
     *
     * The return value will have the structure:
     *
     * [['name'     => name1,
     *   'tmp_name' => tmp1,
     *   'type'     => type1,
     *   'size'     => size1,
     *   'error'    => error1,
     *   ...],
     *  ['name'     => name2,
     *   'tmp_name' => tmp2,
     *   'type'     => type2,
     *   'size'     => size2,
     *   'error'    => error2,
     *   ...],
     *  ...]
     *
     * @return array  Each entry is an associative array for a single file.
     */
    public static function getUploadedFiles(){
        // TODO improve description
        // TODO make it work with any kind of file upload, not only HTML array
        $files = self::transposeArray($_FILES['files']) ?: array();
        return $files == array(array()) ? array() : $files;
    }

    /**
     * Create a new Stud.IP document from an uploaded file.
     *
     * @param  array   $file       Metadata of uploaded file.
     * @param  string  $folder_id  ID of Stud.IP folder to which file is stored.
     * @return StudipDocument      New Stud.IP document for uploaded file.
     * @throws AccessDeniedException if file is forbidden or upload failed.
     */
    public static function fromUpload($file, $folder_id) {
        self::verifyUpload($file);  // throw exception if file is forbidden

        $newfile = \StudipDocument::createWithFile(
            $file['tmp_name'],
            self::studipData($file, $folder_id));

        if (! $newfile) { // file creation failed
            throw new \AccessDeniedException(
                _('Stud.IP-Dokument konnte nicht erstellt werden.'));
        }
        return new WysiwygDocument($newfile, $file['type']);
    }

    /**
     * Throw exception if upload of given file is forbidden.
     *
     * @param Array $file  PHP file info array of uploaded file.
     * @throws AccessDeniedException if file is forbidden by Stud.IP settings.
     */
    private static function verifyUpload($file) {
        $GLOBALS['msg'] = ''; // validate_upload will store messages here
        if (! \validate_upload($file)) { // upload is forbidden
            // remove error pattern from message
            $message = \preg_replace('/error§(.+)§/', '$1', $GLOBALS['msg']);

            // clear global messages and throw exception
            $GLOBALS['msg'] = '';
            throw new \AccessDeniedException(\decodeHTML($message));
        }
    }

    /**
     * Initialize Stud.IP metadata array for creating a new Stud.IP document.
     *
     * @param  array   $file       Metadata of uploaded file.
     * @param  string  $folder_id  ID of folder in which the document is created.
     * @return array   Stud.IP document metadata
     */
    static function studipData($file, $folder_id) {
        $filename = $file['name'];
        return array(
            'name' => $filename,
            'filename' => $filename,
            'user_id' => $GLOBALS['user']->id,
            'author_name' => \get_fullname(),
            'seminar_id' => WysiwygRequest::seminarId(),
            'range_id' => $folder_id,
            'filesize' => $file['size']
        );
    }

    //// folder creation //////////////////////////////////////////////////////

    /**
     * Create a new Stud.IP folder or return an existing one.
     *
     * @param string $name        Folder name.
     * @param string $description Folder description. Only used if folder
     *                            doesn't already exist.
     * @param string $parent_id   Parent folder's ID, NULL for top-level
     *                            folders.
     * @param int    $permission  Folder access permissions.
     * @return string             Folder ID, NULL if something went wrong.
     */
    public function createFolder(
        $name, $description = null
    ) {
        $seminar_id = WysiwygRequest::seminarId();
        $topFolder = \Folder::findTopFolder($seminar_id)->getTypedFolder();
        foreach ($topFolder->getSubfolders() as $subfolder) {
            if ($subfolder->name === $name) {
                return $subfolder;
            }
        }
        $wysiwygfolder = new \StandardFolder();
        $wysiwygfolder->name = $name;
        $wysiwygfolder->description = $description;
        $wysiwygfolder->user_id = $GLOBALS['user']->id;
        return $topFolder->createFolder($wysiwygfolder);
    }



    //// utilities ////////////////////////////////////////////////////////////

    /**
     * Transpose an array of arrays.
     *
     * The input array must be of the form:
     *
     * [0 => [0 => value11, 1 => value12, 2 => value13, ...],
     *  1 => [0 => value21, 1 => value22, ...],
     *  ...]
     *
     * The output array will then have the form:
     *
     * [0 => [0 => value11, 1 => value21, ...],
     *  1 => [0 => value12, 1 => value22, ...],
     *  2 => [0 => value13, ...],
     *  ...]
     *
     * Outer array keys pointing to empty arrays will be removed. For
     * example: Transposing ['a' => []] results in [[]].
     *
     * Note that PHP automatically assigns keys starting at 0 if none are
     * set explicitely. Therefore ['a' => [], [], []] equals
     * ['a' => [], 0 => [], 1 => []].
     *
     * @param array $a  Input, an array of arrays.
     * @returns array   Transposed form of input.
     *                  NULL if input is not an array of arrays.
     */
    private static function transposeArray($a)
    {
        if (!is_array($a)) {
            return null;
        }
        $b = array();
        foreach($a as $rowKey => $row){
            if (!is_array($row)) {
                return null;
            }
            if (empty($row)) {
                $b[] = array();
                continue;
            }
            foreach($row as $columnKey => $value){
                $b[$columnKey][$rowKey] = $value;
            }
        }
        return $b;
    }

    /**
     * Execute a database query and return it's results.
     *
     * Do not use this function to fetch large result sets!
     * Result format is as defined by PDO::ATTR_DEFAULT_FETCH_MODE.
     *
     * @param string  $query       SQL query to execute.
     * @param array   $parameters  Parameters for the SQL query.
     * @param boolean $fetch       If set to FALSE fetchAll() is not executed.
     * @return mixed               Array of result set rows (empty for zero
     *                             results), or PDOStatement if $fetch is FALSE.
     *                             Returns FALSE on failure.
     */
    static function executeQuery($query, $parameters, $fetch = true) {
        $statement = \DBManager::get()->prepare($query);
        if (!$statement->execute($parameters)) {
            return FALSE;
        }
        return $fetch ? $statement->fetchAll() : $statement;
    }
}
