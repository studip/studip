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
 * This is a LibraryResultParser implementation for the K10PlusZentral catalog.
 *
 * @see LibraryResultParser
 */
class K10PlusLibraryResultParser implements LibraryResultParser
{
    /**
     * @see LibraryResultParser::readResultSet
     */
    public function readResultSet($data = '') : array
    {
        //Convert the data to JSON:
        $json_data = json_decode($data, true);

        $header = $json_data['responseHeader'];

        if ($header['status'] != 0) {
            //Probably an error!
            return [];
        }

        if (!$json_data['response']) {
            //JSON structure error!
            return [];
        }

        if (!$json_data['response']['docs'] || ($json_data['response']['numFound'] == 0)) {
            //No data found.
            return [];
        }

        $result_set = [];
        foreach ($json_data['response']['docs'] as $doc_data) {
            $result = $this->readRecord($doc_data);
            if ($result instanceof LibraryDocument) {
                $result_set[] = $result;
            }
        }
        return $result_set;
    }


    /**
     * @see LibraryResultParser::readRecord
     */
    public function readRecord($data = '') : LibraryDocument
    {
        $parser = new MarcxmlLibraryResultParser();
        //The result is one marcxml record in a collection:
        $result = $parser->readResultSet($data['fullrecord_marcxml'])[0];
        //Now we have to set the type using the data from the JSON fields:
        $doc_type = $data['format'][0];
        if (($doc_type == 'Article') || ($doc_type == 'electronic Article')) {
            $result->type = 'article';
        } elseif (($doc_type == 'Book') || ($doc_type == 'eBook')) {
            $result->type = 'book';
        } elseif ($doc_type == 'journal') {
            $result->type = 'article-journal';
        }
        if (!isset($result->csl_data['URL'])) {
            $result->csl_data['URL'] = $data['url'];
        }
        $result->filterCslFieldsByType();
        return $result;
    }
}
