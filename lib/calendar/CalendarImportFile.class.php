<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarImportFile.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calendar
 */

class CalendarImportFile extends CalendarImport
{

    private $file;
    private $path;

    /**
     *
     */
    public function __construct(&$parser, $file, $path = '')
    {
        parent::__construct($parser);
        $this->file = $file;
        $this->path = $path;
    }

    /**
     *
     */
    public function getContent()
    {
        $data = '';
        if (!$file = @fopen($this->file['tmp_name'], 'rb')) {
            throw new CalendarExportException(_("Die Import-Datei konnte nicht geöffnet werden!"));
            return false;
        }
        if ($file) {
            while (!feof($file)) {
                $data .= fread($file, 1024);
            }
            fclose($file);
        }
        return $data;
    }

    /**
     *
     */
    public function getFileName()
    {
        return $this->file['name'];
    }

    /**
     *
     */
    public function getFileType()
    {
        return $this->_parser->getType();
    }

    /**
     *
     */
    public function getFileSize()
    {
        if (file_exists($this->file['tmp_name'])) {
            return filesize($this->file['tmp_name']);
        }
        return false;
    }

    /**
     *
     */
    public function checkFile()
    {
        return true;
    }

    /**
     *
     */
    public function importIntoDatabase($range_id, $ignore = CalendarImport::IGNORE_ERRORS)
    {
        if ($this->checkFile()) {
            parent::importIntoDatabase($range_id, $ignore);
            return true;
        }
        throw new CalendarExportException(_('Die Datei konnte nicht gelesen werden!'));
        return false;
    }

    /**
     *
     */
    public function importIntoObjects($ignore = CalendarImport::IGNORE_ERRORS)
    {
        global $_calendar_error;

        if ($this->checkFile()) {
            parent::importIntoObjects($ignore);
            return true;
        }
        throw new CalendarExportException(_('Die Datei konnte nicht gelesen werden!'));
    }

    /**
     *
     */
    public function deleteFile()
    {
        if (!unlink($this->file['tmp_name'])) {
            throw new CalendarExportException(_("Die Datei konnte nicht gelöscht werden!"));
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function _getFileExtension()
    {
        $i = mb_strrpos($this->file['name'], '.');
        if (!$i) {
            return '';
        }
        $l = mb_strlen($this->file['name']) - $i;
        $ext = mb_substr($this->file['name'], $i + 1, $l);
        return $ext;
    }
}
