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
class MarcxmlLibraryResultParser implements LibraryResultParser
{
    /**
     * This is a helper method to index the child nodes of a node which
     * represent the Marc21 subfields. The index is the subfield code
     * of the DOM child node.
     *
     * @param DOMElement $datafield The node whose child nodes shall be indexed.
     *
     * @returns DOMElement[] An array of DOMElement nodes reperesenting
     * the indexed child nodes of the supplied node.
     */
    protected function indexSubfields(\DOMElement $datafield) : array
    {
        $indexed_subfields = [];
        $subfields = $datafield->getElementsByTagName('subfield');
        foreach ($subfields as $subfield) {
            $code = $subfield->getAttribute('code');
            $indexed_subfields[$code] = $subfield;
        }
        return $indexed_subfields;
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
    public function readResultNode(\DOMElement $node) : LibraryDocument
    {
        $result = new LibraryDocument();

        //Read the control fields:
        $control_nodes = $node->getElementsByTagName('controlfield');
        foreach ($control_nodes as $control_node) {
            $tag = $control_node->getAttribute('tag');
            if ($tag == '001') {
                $result->opac_document_id = trim($control_node->textContent);
            } elseif ($tag == '007') {
                //Get the document type and the year.
                $value = trim($control_node->textContent);
                if ($value[0] == 'a') {
                    //Map: TODO
                } elseif ($value[0] == 'c') {
                    //Electronic resource:
                    $designation = $value[1];
                } elseif ($value[0] == 'd') {
                    //Globe: TODO?
                } elseif ($value[0] == 'f') {
                    //Tactile material: TODO?
                } elseif ($value[0] == 'g') {
                    //Projected graphic: TODO?
                } elseif ($value[0] == 'h') {
                    //Microform: TODO?
                } elseif ($value[0] == 'k') {
                    //Nonprojected graphic: TODO?
                } elseif ($value[0] == 'm') {
                    //Motion picture: TODO
                } elseif ($value[0] == 'o') {
                    //Kit: TODO?
                } elseif ($value[0] == 'q') {
                    //Notated music: TODO?
                } elseif ($value[0] == 'r') {
                    //Remote-sensing image: TODO?
                } elseif ($value[0] == 's') {
                    //Sound recording: TODO?
                } elseif ($value[0] == 't') {
                    //Text:
                    $designation = $value[1];
                    if ($designation == 'a') {
                        //TODO
                    } else {
                        //TODO
                    }
                } elseif ($value[0] == 'v') {
                    //Videorecording: TODO?
                } elseif ($value[0] == 'z') {
                    //Unspecified: TODO
                } else {
                    //All other media types.
                }
            } else {
                //All other field values are stored in the datafields array.
                $subfields = $this->indexSubfields($control_node);
                $subfield_text = [];
                foreach ($subfields as $key => $field) {
                    $subfield_text[] = '"' . $key . '": "' . $field->textContent . '"';
                }
                $result->datafields[$tag] = 'all: "' . $control_node->textContent . '", subfields: {' . implode(', ', $subfield_text) . '}';
            }
        }
        //We have to index the datafield nodes:
        $datafield_nodes = $node->getElementsByTagName('datafield');
        foreach ($datafield_nodes as $datafield_node) {
            $tag = $datafield_node->getAttribute('tag');

            if ($tag == '245') {
                //Title: Get the subfields:
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('a', $subfields)) {
                    if ($subfields['a'] instanceof \DOMElement) {
                        //This is the title.
                        $result->csl_data['title'] = $subfields['a']->textContent;
                    }
                }
            } elseif ($tag == '100') {
                //Author: Get the subfields:
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('a', $subfields)) {
                    $author_data = explode(', ', $subfields['a']->textContent);
                    $result->csl_data['author'] = [
                        [
                            'family' => $author_data[0],
                            'given' => $author_data[1],
                            'suffix' => ''
                        ]
                    ];
                }
            } elseif ($tag == '773') {
                //ISSN: Get the subfields:
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('x', $subfields)) {
                    if ($subfields['x'] instanceof \DOMElement) {
                        $result->csl_data['ISSN'] = $subfields['x']->textContent;
                    }
                }
            } elseif ($tag == '520') {
                //Description: get subfield "a":
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('a', $subfields)) {
                    //TODO: place this in a correct CSL field!
                    $result->csl_data['description'] = $subfields['a']->textContent;
                }
            } elseif ($tag == '856') {
                //Document-URL: get subfield "u":
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('u', $subfields)) {
                    $result->csl_data['URL'] = $subfields['u']->textContent;
                }
            } elseif ($tag == '020') {
                //ISBN: get subfield "a":
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('a', $subfields)) {
                    $result->csl_data['ISBN'] = $subfields['a']->textContent;
                }
            } elseif ($tag == '024') {
                //Identifier: get subfield "a":
                $subfields = $this->indexSubfields($datafield_node);
                if (array_key_exists('a', $subfields)) {
                    $result->csl_data['DOI'] = $subfields['a']->textContent;
                }
            } else {
                //All other field values are stored in the datafields array.
                $subfields = $this->indexSubfields($datafield_node);
                $subfield_text = [];
                foreach ($subfields as $key => $field) {
                    $subfield_text[] = '"' . $key . '": "' . $field->textContent . '"';
                }
                $result->datafields[$tag] = 'all: "' . $datafield_node->textContent . '", subfields: {' . implode(', ', $subfield_text) . '}';
            }
        }

        return $result;
    }


    /**
     * @see LibraryResultParser::readResultSet
     */
    public function readResultSet($data = '') : array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($data);
        $collection = $dom->getElementsByTagName('collection')[0];
        if (!$collection) {
            //Wrong document type.
            return [];
        }

        $records = $collection->getElementsByTagName('record');

        $result_set = [];
        foreach ($records as $record) {
            $result_set[] = $this->readResultNode($record);
        }
        return $result_set;
    }


    /**
     * @see LibraryResultParser::readRecord
     */
    public function readRecord($data = '') : LibraryDocument
    {
        $dom = \DOMDocument::loadXML($data);
        $record = $dom->getElementsByTagName('record')[0];
        if ($record instanceof \DOMElement) {
            return $this->readResultNode($record);
        }
        return null;
    }
}
