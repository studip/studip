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
 * This is a LibrarySearch implementation for catalogs that use SRU.
 *
 * @see LibrarySearch
 */
class SRULibrarySearch extends LibrarySearch
{
    /**
     * This array is used to map the general search field names to
     * the real field names.
     */
    protected static $field_replacements = [
        'pica' => [
            LibrarySearch::TITLE       => 'pica.tit',
            LibrarySearch::AUTHOR      => 'pica.per',
            LibrarySearch::YEAR        => 'pica.jhr',
            LibrarySearch::NUMBER      => 'pica.num',
            LibrarySearch::ISBN        => 'pica.isb',
            LibrarySearch::ISSN        => 'pica.iss',
            LibrarySearch::PUBLICATION => 'pica.gti',
            LibrarySearch::SIGNATURE   => 'pica.sga'
        ],
        'cql' => [
            LibrarySearch::TITLE       => 'dc.title',
            LibrarySearch::AUTHOR      => 'dc.creator',
            LibrarySearch::YEAR        => 'dc.date',
            LibrarySearch::NUMBER      => 'dc.identifier',
            LibrarySearch::ISBN        => 'dc.identifier',
            LibrarySearch::ISSN        => 'dc.identifier'
        ]
    ];


    /**
     * @see LibrarySearch::translateQueryFields
     */
    protected function translateQueryFields(array $query_fields = []) : array
    {
        if (!$query_fields) {
            return [];
        }

        $query_format = 'pica';
        if ($this->settings['query_format'] == 'cql') {
            $query_format = 'cql';
        }

        $translated_fields = [];
        foreach ($query_fields as $key => $value) {
            if (in_array($key, array_keys(self::$field_replacements[$query_format]))) {
                $new_key = self::$field_replacements[$query_format][$key];
                $translated_fields[$new_key] = $value;
            } else {
                $translated_fields[$key] = $value;
            }
        }
        return $translated_fields;
    }


    /**
     * @see LibrarySearch::query
     */
    public function query(
        array $search_parameters = [],
        string $order_by = self::ORDER_BY_RELEVANCE,
        int $limit = 200
    ) : array
    {
        if (!$search_parameters) {
            return [];
        }

        //The standardised parameter names must be converted to module-specific
        //search parameter names before being added to the query string.
        $search_parameters = $this->translateQueryFields($search_parameters);
        $query_string = '';
        $query_format = 'pica';
        if ($this->settings['query_format'] == 'cql') {
            $query_format = 'cql';
        }
        foreach ($search_parameters as $key => $value) {
            if (!empty($query_string)) {
                $query_string .= ' and ';
            }
            if ($key == self::$field_replacements[$query_format][LibrarySearch::NUMBER]) {
                $query_string .= sprintf(
                    '(%1$s="%2$s" or %3$s="%2$s" or %4$s="%2$s")',
                    self::$field_replacements[$query_format][LibrarySearch::ISBN],
                    addslashes($value),
                    self::$field_replacements[$query_format][LibrarySearch::ISSN],
                    self::$field_replacements[$query_format][LibrarySearch::NUMBER]
                );
            } else {
                //TODO: escape colon in data!
                $query_string .= sprintf('%1$s="%2$s"', $key, addslashes($value));
            }
        }

        $query_parameters = $this->additional_query_parameters;
        $query_parameters['version'] = '1.1'; //TODO: is version 2.0 supported?
        $query_parameters['operation'] = 'searchRetrieve';
        $query_parameters['recordSchema'] = 'marcxml';
        if ($this->settings['sru_version'] == '1.2') {
            $query_parameters['version'] = '1.2';
            //Use SRU/SRW 1.2
            if ($order_by == self::ORDER_BY_RELEVANCE) {
                if ($query_format != 'cql') {
                    $query_string .= ' sortby relevance/descending';
                }
            } elseif ($order_by == self::ORDER_BY_YEAR) {
                if ($query_format == 'cql') {
                    $query_string .= ' sortBy dc.date/sort.descending';
                } else {
                    $query_string .= ' sortby year/descending';
                }
            }
        } else {
            //Use SRU/SRW 1.1
            if ($order_by == self::ORDER_BY_RELEVANCE) {
                $query_parameters['sortKeys'] = 'relevance,,0';
            } elseif ($order_by == self::ORDER_BY_YEAR) {
                $query_parameters['sortKeys'] = 'year,,0';
            }
        }
        $query_parameters['maximumRecords'] = $limit;
        $query_parameters['query'] = $query_string;
        $data = $this->requestData($this->request_base_url, $query_parameters);
        if (!$data) {
            //There are no data we can retrieve.
            return [];
        }
        $parser = new SRULibraryResultParser();
        return $parser->readResultSet($data);
    }
}
