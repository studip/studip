<?php
/**
 * MvvFileFileref.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 */

class MvvFileFileref extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_files_filerefs';

        $config['belongs_to']['mvv_file'] = array(
            'class_name' => 'MvvFile',
            'foreign_key' => 'mvvfile_id',
            'assoc_func' => 'findCached',
        );

        $config['belongs_to']['file_ref'] = array(
            'class_name' => 'FileRef',
            'foreign_key' => 'fileref_id',
        );
        $config['additional_fields']['filetype']['get'] = 'getFiletype';
        $config['additional_fields']['filename']['get'] = 'getFilename';


        parent::configure($config);
    }

    /**
     * Returns the filename of the document based on its fileref.
     *
     * @return string Name of the file.
     */
    public function getFilename()
    {
        if ($this->file_ref) {
            $filetype = $this->file_ref->getFileType();
            return $filetype->getFilename();
        }
    }

    /**
     * Returns the filetype of the document based on its mimetype.
     *
     * @return bool|string Returns false on failure, otherwise the name of the filetype.
     */
    public function getFiletype()
    {
        $application_category = [
            'PDF'     => ['pdf'],
            'Powerpoint'     => ['powerpoint','presentation'],
            'Excel'   => ['excel', 'spreadsheet', 'csv'],
            'Word'    => ['word', 'wordprocessingml', 'opendocument.text', 'rtf'],
            'Archiv' => ['zip', 'rar', 'arj', '7z'],
        ];

        if (!$this->file_ref) {
            return '';
        }
        $filetype = $this->file_ref->getFileType();
        if ($filetype instanceof URLFile) {
            return _('Link');
        } else {
            $mime_type = $filetype->getMimeType();
            if (!$mime_type) {
                return _('sonstiges');
            } else {
                list($category, $type) = explode('/', $mime_type, 2);
                if ($category == 'application') {
                    foreach ($application_category as $name => $type_name) {
                        if (preg_match('/' . implode($type_name, '|') . '/i', $type)) {
                            return $name;
                        }
                    }
                } elseif ($category == 'image') {
                    return _('Bild');
                } else {
                    return $category;
                }
            }
        }
    }
}
