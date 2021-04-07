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
    private $mapping = [
        '001' => ['field' => 'id', 'callback' => 'simpleMap', 'cb_args' => ''],
        '008' => [
            ['field' => 'language', 'callback' => 'simpleFixFieldMap', 'cb_args' => ['start' => 35, 'length' => 3]],
            ['field' => 'issued', 'callback' => 'simpleFixFieldMap', 'cb_args' => ['start' => 7, 'length' => 4], 'format' => 'date']
        ],
        '020' => ['field' => 'ISBN', 'callback' => 'simpleMap', 'cb_args' => '$a'],
        '245' => ['field' => 'title', 'callback' => 'simpleMap', 'cb_args' => '$a $b $h'],
        '264' => ['field' => 'publisher', 'callback' => 'simpleMap', 'cb_args' => '$a $b'],
        '256' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$a' . "\n"],
        '300' => ['field' => 'medium', 'callback' => 'simpleMap', 'cb_args' => '$a $b $c $e'],
        '440' => ['field' => 'container-title', 'callback' => 'simpleMap', 'cb_args' => '$a $v'],
        '500' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$a' . "\n"],
        '502' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$a' . "\n"],
        '518' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$a' . "\n"],
        '520' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$a' . "\n"],
        '533' => ['field' => 'note', 'callback' => 'simpleMap', 'cb_args' => '$n' . "\n"],
        '600' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '610' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '611' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '630' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '650' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => '$a'],
        '651' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '652' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '653' => ['field' => 'note', 'callback' => 'simpleListMap', 'cb_args' => false],
        '773' => [
            ['field' => 'publisher', 'callback' => 'simpleMap', 'cb_args' => '$t, $g, $d'],
            ['field' => 'ISSN', 'callback' => 'simpleMap', 'cb_args' => '$x'],
        ],
        '100' => ['field' => 'author', 'callback' => 'simpleMap', 'cb_args' => '$a', 'format' => 'name'],
        '700' => ['field' => 'author', 'callback' => 'notEmptyMap', 'cb_args' => ['$a', 'contributor', '$a;'], 'format' => 'name'],
        '110' => ['field' => 'author', 'callback' => 'simpleMap', 'cb_args' => '$a, $b', 'format' => 'name'],
        '111' => ['field' => 'author', 'callback' => 'notEmptyMap', 'cb_args' => ['$a, $b', 'contributor', '$a, $b;'], 'format' => 'name'],
        '710' => ['field' => 'author', 'callback' => 'notEmptyMap', 'cb_args' => ['$a, $b', 'contributor', '$a, $b;'], 'format' => 'name'],
        '711' => ['field' => 'author', 'callback' => 'notEmptyMap', 'cb_args' => ['$a, $b', 'contributor', '$a, $b;'], 'format' => 'name'],
        '856' => ['field' => 'URL', 'callback' => 'notEmptyMap', 'cb_args' => ['$u', 'URL2', '$u']],
    ];

    function simpleListMap($doc, $data, $field, $args = [], $format = '')
    {
        if (is_array($data)) {
            $result = join('; ', $data);
        } else {
            $result = $data;
        }
        $result = (($doc->csl_data[$field])) ? $doc->csl_data[$field] . '; ' . $result : $result;
        $doc->csl_data[$field] = trim($result);
    }

    function simpleFixFieldMap($doc, $data, $field, $args = [], $format = '')
    {
        if (is_array($args) && $data != "") {
            if ($result = trim(mb_substr($data, $args['start'], $args['length']))) {
                if ($format === 'date') {
                    $result = ['date-parts' => [[$result, 1, 1]]];
                    $doc->csl_data[$field] = $result;
                } else {
                    $doc->csl_data[$field] = trim($doc->csl_data[$field] . " " . $result);
                }
            }
        }
    }

    function notEmptyMap($doc, $data, $field, $args, $format = '')
    {
        if (empty($doc->csl_data[$field])) {
            $this->simpleMap($doc, $data, $field, $args[0], $format);
        } else {
            $this->simpleMap($doc, $data, $args[1], $args[2], $format);
        }
        return;
    }

    function simpleMap($doc, $data, $field, $args, $format = '')
    {
        $trim_chars = " \t\n\r\0/,:.";
        if ($args != "" && is_array($data)) {
            foreach ($data as $key => $value) {
                $search[] = '$' . $key;
                $replace[] = $value;
            }
            $result = str_replace($search, $replace, $args);
            $result = preg_replace('/\$[0-9a-z]\s*/', "", $result);

        } else {
            $result = $data;
        }
        $result = trim($result, $trim_chars);
        if ($format == 'name') {
            $author_data = explode(', ', $result);
            $result =
                [
                    'family' => $author_data[0],
                    'given'  => $author_data[1],
                    'suffix' => ''
                ];

            $doc->csl_data[$field][] = $result;
        } else {
            $doc->csl_data[$field] = trim($doc->csl_data[$field] . " " . $result);
        }
    }

    /**
     * @see LibraryResultParser::readResultSet
     */
    public function readResultSet($data = ''): array
    {
        //echo '<pre>'. htmlready(print_r($data,1)) . '</pre>';
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
     * Reads the XML nodes of one record and creates a LibraryDocument
     * out of it.
     *
     * @param DOMElement $node The XML node of one record.
     *
     * @returns LibraryDocument The LibraryDocument instance that could be read
     *     from the data.
     */
    public function readResultNode(\DOMElement $node): LibraryDocument
    {
        $result = new LibraryDocument();



        $xmlrecord = simplexml_import_dom($node);
        $plugin_mapping = $this->mapping;
        foreach ($xmlrecord->controlfield as $field) {
            $code = (string)$field['tag'];
            $data = (string)$field;
            if (isset($plugin_mapping[$code])) {
                $mapping = (is_array($plugin_mapping[$code][0])) ? $plugin_mapping[$code] : [$plugin_mapping[$code]];
                for ($j = 0; $j < count($mapping); ++$j) {
                    $map_method = $mapping[$j]['callback'];
                    $this->$map_method($result, $data, $mapping[$j]['field'], $mapping[$j]['cb_args'], $mapping[$j]['format']);
                }
            }
        }
        foreach ($xmlrecord->datafield as $field) {
            $code = (string)$field['tag'];
            $data = [];
            foreach ($field->subfield as $subfield) {
                $subcode = (string)$subfield['code'];
                if ($subcode && !isset($data[$subcode])) {
                    $data[$subcode] = (string)$subfield;
                }
            }
            if (isset($plugin_mapping[$code])) {
                $mapping = (is_array($plugin_mapping[$code][0])) ? $plugin_mapping[$code] : [$plugin_mapping[$code]];
                for ($j = 0; $j < count($mapping); ++$j) {
                    $map_method = $mapping[$j]['callback'];
                    $this->$map_method($result, $data, $mapping[$j]['field'], $mapping[$j]['cb_args'], $mapping[$j]['format']);
                }
            }
        }
        return $result;
    }

    /**
     * @see LibraryResultParser::readRecord
     */
    public function readRecord($data = ''): LibraryDocument
    {
        $dom = new \DOMDocument();
        $dom->loadXML($data);
        $record = $dom->getElementsByTagName('record')[0];
        if ($record instanceof \DOMElement) {
            return $this->readResultNode($record);
        }
    }

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
    protected function indexSubfields(\DOMElement $datafield): array
    {
        $indexed_subfields = [];
        $subfields = $datafield->getElementsByTagName('subfield');
        foreach ($subfields as $subfield) {
            $code = $subfield->getAttribute('code');
            $indexed_subfields[$code] = $subfield;
        }
        return $indexed_subfields;
    }
}
