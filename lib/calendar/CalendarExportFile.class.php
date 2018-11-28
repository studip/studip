<?
# Lifter002: TODO
# Lifter007: TODO

/**
 * CalendarExportFile.class.php
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

class CalendarExportFile extends CalendarExport
{
    private $file_name = 'studip';
    private $tmp_file_name;
    private $path;

    public function __construct(&$writer, $path = null, $file_name = null)
    {
        global $TMP_PATH;

        parent::__construct($writer);

        if (!$file_name) {
            $this->tmp_file_name = $this->makeUniqueFilename();
            $this->file_name .= '.' . $writer->getDefaultFileNameSuffix();
        } else {
            $this->file_name = $file_name;
            $this->tmp_file_name = $file_name;
        }

        if (!$path) {
            $this->path = $TMP_PATH . '/';
        }

        $this->_writer = $writer;
    }

    public function exportFromDatabase($range_id = null, $start = 0, $end = Calendar::CALENDAR_END, $event_types = null, $except = null)
    {
        $this->_createFile();
        parent::exportFromDatabase($range_id, $start, $end, $event_types, $sem_ids, $except);
        $this->_closeFile();
    }

    public function exportFromObjects($events)
    {
        $this->_createFile();
        parent::exportFromObjects($events);
        $this->_closeFile();
    }

    public function sendFile()
    {
        if (file_exists($this->path . $this->tmp_file_name)) {
            header('Location: ' . FileManager::getDownloadURLForTemporaryFile($this->tmp_file_name, $this->file_name));
        } else {
            throw new CalendarExportException(_('Die Export-Datei konnte nicht erstellt werden!'));
        }
    }

    public function makeUniqueFileName()
    {
        return md5(uniqid(rand() . "Stud.IP Calendar"));
    }

    // returns file handle
    public function getExport()
    {
        return $this->export;
    }

    public function getFileName()
    {
        return $this->file_name;
    }

    public function getTempFileName()
    {
        return $this->tmp_file_name;
    }

    public function _createFile()
    {
        global $_calendar_error;

        if (!(is_dir($this->path))) {
            if (!mkdir($this->path)) {
                var_dump($this->path); exit;
                throw new CalendarExportException(_('Das Export-Verzeichnis konnte nicht angelegt werden!'));
            } else {
                if (!chmod($this->path, 0777)) {
                    throw new CalendarExportException(_('Die Zugriffsrechte auf das Export-Verzeichnis konnten nicht geändert werden!'));
                }
            }
        }
        if (file_exists($this->path . $this->tmp_file_name)) {
            if (!unlink($this->path . $this->tmp_file_name)) {
                throw new CalendarExportException(_('Eine bestehende Export-Datei konnte nicht gelöscht werden!'));
            }
        }
        $this->export = fopen($this->path . $this->tmp_file_name, "wb");
        if (!$this->export) {
            throw new CalendarExportException(_("Die Export-Datei konnte nicht erstellt werden!"));
        }
    }

    public function _export($exp)
    {
        fwrite($this->export, $exp);
    }

    public function _closeFile()
    {
        fclose($this->export);
    }
}
