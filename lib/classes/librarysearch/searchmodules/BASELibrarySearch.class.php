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
 * This is a LibrarySearch implementation for the BASE catalog.
 *
 * @see LibrarySearch
 */
class BASELibrarySearch extends LibrarySearch
{
    /**
     * This array is used to map the general search field names to
     * the real field names.
     */
    protected static $field_replacements = [
        LibrarySearch::TITLE       => 'dctitle',
        LibrarySearch::AUTHOR      => 'dccreator',
        LibrarySearch::YEAR        => 'dcyear',
        LibrarySearch::NUMBER      => 'dcrelation',
        LibrarySearch::ISSN        => 'dcrelation', //No special ISSN field available.
        LibrarySearch::ISBN        => 'dcrelation', //No special ISBN field available.
        LibrarySearch::PUBLICATION => 'dcpublisher',
        LibrarySearch::SIGNATURE   => 'dcdocid'
    ];


    /**
     * @see LibrarySearch::translateQueryFields
     */
    protected function translateQueryFields(array $query_fields = []) : array
    {
        if (!$query_fields) {
            return [];
        }

        $translated_fields = [];
        foreach ($query_fields as $key => $value) {
            if (in_array($key, array_keys(self::$field_replacements))) {
                $new_key = self::$field_replacements[$key];
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
        int $limit = 125
    ) : array
    {
        if (!$search_parameters) {
            return [];
        }

        //The standardised parameter names must be converted to module-specific
        //search parameter names before being added to the query string.
        $search_parameters = $this->translateQueryFields($search_parameters);
        $query_string = '';
        foreach ($search_parameters as $key => $value) {
            if (!empty($query_string)) {
                $query_string .= ' AND ';
            }
            //TODO: escape colon in data!
            $query_string .= sprintf('%1$s:(%2$s)', $key, $value);
        }

        $query_parameters = $this->additional_query_parameters;
        $query_parameters['func'] = 'PerformSearch';
        $query_parameters['coll'] = $this->settings['collection']
                                  ? $this->settings['collection']
                                  : 'de';
        $query_parameters['query'] = $query_string;
        //Order by relevance is the default in BASE.
        //No URL parameter needed in that case.
        if ($order_by == self::ORDER_BY_YEAR) {
            $query_parameters['sortby'] = 'dcyear desc';
        }
        if ($limit > 0) {
            //BASE has a limit of 125 items per response.
            if ($limit > 125) {
                $limit = 125;
            }
            $query_parameters['hits'] = $limit;
        }
        $data = $this->requestData($this->request_base_url, $query_parameters);
        if ($data === null) {
            //There are no data we can retrieve.
            return [];
        }
        $parser = new BASELibraryResultParser();
        return $parser->readResultSet($data);
    }
}
