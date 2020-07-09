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
 * This is a LibraryResultParser implementation for catalogs that
 * use SRU.
 *
 * @see LibraryResultParser
 */
class SRULibraryResultParser implements LibraryResultParser
{
    /**
     * @see LibraryResultParser::readResultSet
     */
    public function readResultSet($data = '') : array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($data);
        $result = $dom->getElementsByTagName('records')[0];
        if (!$result) {
            //Wrong document type.
            return [];
        }

        $records = $result->childNodes;
        $result_set = [];
        foreach ($records as $record) {
            $record_schema = $record->getElementsByTagName('recordSchema')[0];
            if ($record_schema->textContent == 'marcxml') {
                $record_node = $record->getElementsByTagName('record')[0];
                if (!$record_node) {
                    //Invalid data.
                    continue;
                }
                $parser = new MarcxmlLibraryResultParser();
                //The result is one marcxml record in a collection:
                $result_set[] = $parser->readResultNode($record_node);
            } else {
                //Unknown format
            }
        }
        return $result_set;
    }


    /**
     * @see LibraryResultParser::readRecord
     */
    public function readRecord($data = '') : LibraryDocument
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($data);
        $record = $dom->getElementsByTagName('zs:record')[0];
        if (!$record) {
            //Wrong document type.
            throw new Exception('Wrong document type!');
        }

        $record_schema = $record->getElementsByTagName('zs:recordSchema')[0];
        if ($record_schema->textContent == 'marcxml') {
            $record_node = $record->getElementsByTagName('record')[0];
            if (!$record_node) {
                throw new Exception('Invalid data!');
            }
            $parser = new MarcxmlLibraryResultParser();
            return $parser->readResultNode($record_node);
        }
    }
}
