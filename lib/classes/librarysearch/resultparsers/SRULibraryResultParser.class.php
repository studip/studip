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

        $record_schema = $dom->getElementsByTagName('recordSchema')[0];
        if (!$record_schema) {
            //The recordSchema element is missing.
            //We cannot continue.
            return [];
        }
        if (strpos($record_schema->textContent, 'marcxml') !== false) {
            $parser = new MarcxmlLibraryResultParser();
            $result_set = [];
            $collection_nodes = $dom->getElementsByTagName('collection');
            if ($collection_nodes->length < 1) {
                $collection_nodes = $dom->getElementsByTagName('records');
            }
            if ($collection_nodes->length > 0) {
                foreach ($collection_nodes as $collection) {
                    if ($collection instanceof \DOMText) {
                        //Nothing we can do with text nodes.
                        continue;
                    }
                    foreach ($collection->getElementsByTagName('record') as $record) {
                        $document = $parser->readResultNode($record);
                        if ($document->getTitle()) {
                            $result_set[] = $document;
                        }
                    }
                }
            }
            return $result_set;
        }
        throw new RuntimeException('only recordSchema marcxml implemented');
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
            $document = $parser->readResultNode($record);
            if ($document->getTitle()) {
                return $document;
            } else {
                return null;
            }
        }
    }
}
