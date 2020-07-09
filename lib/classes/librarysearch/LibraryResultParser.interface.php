<?php
/**
 * This file is part of Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2020
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.6
 */


/**
 * This interface defines methods for reading search results from a
 * library catalog.
 */
interface LibraryResultParser
{
    /**
     * Read a set of search results from raw data.
     *
     * @returns LibraryDocument[] An array with all LibraryDocument
     *     instances that could be read from the raw data.
     */
    public function readResultSet($data = '') : array;


    /**
     * Reads one search result record from raw data.
     *
     * @returns LibraryDocument|null The read data as LibraryDocument.
     *     null is returned if no document could be read.
     */
    public function readRecord($data = '') : LibraryDocument;
}
