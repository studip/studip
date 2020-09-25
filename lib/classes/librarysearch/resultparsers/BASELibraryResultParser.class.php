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
 * This is a LibraryResultParser implementation for the BASE catalog.
 *
 * @see LibraryResultParser
 */
class BASELibraryResultParser implements LibraryResultParser
{
    /**
     * This is a helper method to index the child nodes of a node which makes
     * accessing them easier. The index is like the "name" attribute
     * of the DOM child node.
     *
     * @param DOMElement $node The node whose child nodes shall be indexed.
     *
     * @returns DOMElement[] An array of DOMElement nodes reperesenting
     * the indexed child nodes of the supplied node.
     */
    protected function indexChildren(\DOMElement $node)
    {
        $indexed_children = [];
        $children = $node->childNodes;
        foreach ($children as $child) {
            $index = $child->getAttribute('name');
            $indexed_children[$index] = $child;
        }
        return $indexed_children;
    }


    /**
     * Reads the XML nodes of one record and creates a LibraryDocument
     * out of it.
     *
     * @param DOMElement $node The XML node of one record.
     *
     * @returns LibraryDocument The LibraryDocument instance that could be read
     *     from the data.
     */
    protected function readXMLRecord(\DOMElement $node) : LibraryDocument
    {
        $result = new LibraryDocument();

        $children = $this->indexChildren($node);

        foreach ($children as $name => $child) {
            if ($child instanceof \DOMElement) {
                if ($name == 'dctypenorm') {
                    //Set the document type by the value of this field.
                    $base_typeid = trim($child->textContent);
                    if ($base_typeid == '11' || $base_typeid == '111') {
                        $result->type = 'book';
                    } elseif ($base_typeid == '12' || $base_typeid == '121' || $base_typeid == '122') {
                        $result->type = 'article';
                    } elseif ($base_typeid == '13') {
                        $result->type = 'paper-conference';
                    } elseif ($base_typeid == '14') {
                        $result->type = 'report';
                    } else {
                        $result->type = $base_typeid;
                    }
                } elseif ($name == 'dctitle') {
                    $result->csl_data['title'] = $child->textContent;
                } elseif ($name == 'dccreator') {
                    $authors = $child->getElementsByTagName('str');
                    $csl_authors = [];
                    foreach ($authors as $author) {
                        $author_names = explode(', ', $author->textContent);
                        $csl_authors[] = [
                            'family' => $author_names[0],
                            'given' => $author_names[1],
                            'suffix' => ''
                        ];
                    }
                    $result->csl_data['author'] = $csl_authors;
                } elseif ($name == 'dcdate') {
                    $date_and_time = explode('T', $child->textContent);
                    $date_parts = explode('-', $date_and_time[0]);
                    $result->csl_data['issued'] = ['date-parts' => [$date_parts]];
                } elseif (($name == 'dcyear') && !($result->csl_data['issued'])) {
                    $result->csl_data['issued'] = ['date-parts' => [$child->textContent]];
                } elseif ($name == 'dcdescription') {
                    $result->csl_data['abstract'] = $child->textContent;
                } elseif ($name == 'dcpublisher') {
                    $str = $child->childNodes[0];
                    if ($str instanceof \DOMElement) {
                        $result->csl_data['publisher'] = $str->textContent;
                    }
                } elseif ($name == 'dclink') {
                    $result->csl_data['URL'] = $child->textContent;
                } elseif ($name == 'dcdocid') {
                    $result->csl_data['DOI'] = $child->textContent;
                } else {
                    //All other field values are stored in the datafields array.
                    $result->datafields[$name] = $child->textContent;
                }
            }
        }
        return $result;
    }


    /**
     * @see LibraryResultParser::readResultSet
     */
    public function readResultSet($data = '') : array
    {
        if (!$data) {
            return [];
        }
        $dom = new \DOMDocument();
        @$dom->loadXML($data);
        $result = $dom->getElementsByTagName('response')[0];
        if (!$result) {
            //Wrong document type.
            return [];
        }

        $documents = $result->getElementsByTagName('doc');
        $result_set = [];
        foreach ($documents as $document) {
            $result_set[] = $this->readXMLRecord($document);
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
        $record = $dom->getElementsByTagName('doc')[0];
        if ($record) {
           return $this->readXMLRecord($record);
        }
    }
}
