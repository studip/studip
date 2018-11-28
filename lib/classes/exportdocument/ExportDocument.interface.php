<?php
# Lifter010: TODO

/**
 * ExportDocument.interface.php - create and export or save a pdf with simple HTML-Data
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse & Peter Thienel
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This interface describes a basic structure for a class that exports content
 * or many contents within a document. It is used in ExportPDF, but could be
 * used for classes that export Excel or OpenDocument files as well. The main
 * operation to get a document by stud.ip-formatted text is quite simple:
 *
 *  $doc = new ExportPDF();
 *  $doc->addPage();
 *  $doc->addContent('Hallo, %%wir%% benutzen :studip: -Formatierung.');
 *  $doc->dispatch("test_pdf");
 *  //lines following dispatch won't be accessed anymor, because dispatch 
 *  //cancels all other output.
 *
 */
interface ExportDocument {

    /**
     * Adding a new page to write new content on it. Must be called at least once
     * before any call of addContent($text).
     */
    public function addPage();

    /**
     * Adding an area of Stud.IP formatted content.
     */
    public function addContent($content);

    /**
     * Outputs the content as a file with MIME-type and aborts any other output.
     * @param string $filename name of the future file without the extension.
     */
    public function dispatch($filename);

    /**
     * Saves the content as a file in the filesystem and returns a Stud.IP-document object.
     * @param string $filename name of the future file without the extension.
     * @param mixed $folder_id md5-id of a given folder in database or null for nothing
     * @return StudipDocument of the exported file or false if creation of StudipDocument failed.
     */
    public function save($filename, $folder_id = null);

}

