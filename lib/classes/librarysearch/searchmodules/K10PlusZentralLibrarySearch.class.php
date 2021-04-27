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
 * This is a LibrarySearch implementation for the K10PlusZentral catalog.
 *
 * @see LibrarySearch
 */
class K10PlusZentralLibrarySearch extends LibrarySearch
{
    /**
     * This array is used to map the general search field names to
     * the real field names.
     */
    protected static $field_replacements = [
        LibrarySearch::TITLE       => 'title',
        LibrarySearch::AUTHOR      => 'author',
        LibrarySearch::YEAR        => 'publishDate',
        LibrarySearch::NUMBER      => 'number',
        LibrarySearch::ISSN        => 'issn',
        LibrarySearch::ISBN        => 'isbn',
        LibrarySearch::PUBLICATION => 'journal',
        LibrarySearch::SIGNATURE   => 'signature'
    ];


    /**
     * This is a helper method to get LibrarySearch instances from the
     * raw result data of the query.
     *
     * @param string $data Raw query response data.
     *
     * @returns LibraryDocument[] An array of LibraryDocument instaces that
     *     could be read.
     */
    protected function extractResponseData($data)
    {
        $parser = new K10PlusLibraryResultParser();
        $result_set = $parser->readResultSet($data);
        return $result_set;
    }


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

        foreach ($search_parameters as $key => $value) {
            if (!empty($query_string)) {
                $query_string .= ' AND ';
            }
            if ($key == self::$field_replacements[LibrarySearch::NUMBER]) {
                $query_string .= sprintf(
                    '(%1$s:"%2$s" OR %3$s:"%2$s")',
                    self::$field_replacements[LibrarySearch::ISSN],
                    $this->escapeQueryChars($value),
                    self::$field_replacements[LibrarySearch::ISBN]
                );
            } else {
                //TODO: escape colon in data!
                $value = '(' . $this->escapeQueryChars($value) . ')';
                $query_string .= sprintf('%1$s:%2$s', $key, $value);
            }
        }

        $query_parameters = $this->additional_query_parameters;
        //Special handling for the query parameter:
        $query_parameters['q'] = $query_string;
        if ($order_by = self::ORDER_BY_YEAR) {
            $query_parameters['sort'] = 'publishDate desc';
        } else {
            $query_parameters['sort'] = 'score desc';
        }
        if ($limit > 0) {
            $query_parameters['rows'] = $limit;
        }
        //$query_parameters['debug'] = 'query';
        $query_parameters['df'] = self::$field_replacements[LibrarySearch::TITLE];
        $data = $this->requestData($this->request_base_url, $query_parameters);
        if ($data === null) {
            //There are no data we can retrieve.
            return [];
        }
        $result_objects = $this->extractResponseData($data);
        return $result_objects;
    }

    public function escapeQueryChars($str)
    {
        $reserved = preg_quote('+-&|!(){}[]^"~*?:\\');
        return preg_replace_callback(
            '/([' . $reserved . '])/',
            function ($matches) {
                return '\\' . $matches[0];
            },
            $str
        );
    }
}
