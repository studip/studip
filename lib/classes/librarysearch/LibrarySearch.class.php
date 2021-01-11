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
 * This class contains basic methods for querying a library catalog
 * using standardised search parameters.
 */
abstract class LibrarySearch
{
    //The following constants define the strings for the
    //standardised field names for the query method. These can be
    //converted to library-specific field names.
    const TITLE       = 'title';
    const AUTHOR      = 'author';
    const YEAR        = 'year';
    const NUMBER      = 'number';
    const ISSN        = 'issn';
    const ISBN        = 'isbn';
    const PUBLICATION = 'publication';
    const SIGNATURE   = 'signature';

    //Constants for the ordering of results.
    const ORDER_BY_RELEVANCE = 'relevance';
    const ORDER_BY_YEAR = 'year';


    /**
     * The base URL for the HTTP request to retrieve data.
     */
    protected $request_base_url = '';


    /**
     * Additional URL parameters for the HTTP request to retrieve data.
     */
    protected $request_url_parameters = [];


    /**
     * Implementation-specific configuration that can define the behavior
     * of the LibrarySearch implementation.
     */
    protected $settings = [];


    /**
     * A basic constructor.
     *
     * @param array $configuration The configuration for the LibrarySearch
     *     implementation. It should be an associative array with the following
     *     keys:
     *     - base_url: The base URL for retrieving data.
     *     - additional_url_parameters: Additional URL parameters for the base URL.
     *     - settings: Implementation-specific configuration. This should also
     *         be an associative array.
     */
    public function __construct(array $configuration = [])
    {
        if ($configuration['base_url']) {
            $this->request_base_url = $configuration['base_url'];
        }
        if (is_array($configuration['additional_url_parameters'])) {
            $this->request_url_parameters = $configuration['additional_url_parameters'];
        }
        if (is_array($configuration['settings'])) {
            $this->settings = $configuration['settings'];
        }
    }


    /**
     * This method shall replace the generalised search query fields with the
     * implementation specific query fields.
     *
     * @param array $query_fields An array with query parameters using the
     *     generalised query fields.
     *
     * @returns array The translated version of the $query_fields array.
     */
    abstract protected function translateQueryFields(array $query_fields = []) : array;


    /**
     * A common method for the libcurl code to request data from an URL so that
     * LibrarySearch implementations don't have to include their own libcurl
     * code to get data.
     *
     * @param string $base_url The base URL to request data from.
     *
     * @param array $url_parameters URL parameters for the request. The array
     *     should consist of an associative array with keys representing
     *     the parameter name and the values representing the parameter values.
     *
     * @returns string|bool The result of the request. If the base URL is empty
     *     or no data could be retrieved due to an error, false is returned.
     *     In case of success, a string with the retrieved data is returned.
     */
    protected function requestData(string $base_url = '', array $url_parameters = [])
    {
        if (!$base_url) {
            return false;
        }
        $full_url = $base_url;
        if ($url_parameters) {
            $full_url .= '?' . http_build_query($url_parameters);
        }

        $data = file_get_contents($full_url, false, get_default_http_stream_context($base_url));
        return $data;
    }


    /**
     * Starts a query to a library catalogue using the specified
     * parameters. If standardised parameters as defined in the FIELD_
     * constants of this class are used as keys in the $search_parameters array,
     * their keys may be converted to library-specific search keys.
     *
     * @param array $search_parameters The search parameters to be used.
     *     The array must be an associative array where the keys represent
     *     the fields.
     *
     * @param string $order_by
     *
     * @param int $limit The maximum amount of items that shall be retrieved
     *     from the catalog.
     *
     * @returns LibrarySearchResult[] An array of LibrarySearchResult items
     *     if entries matching the search could be found in the library.
     */
    abstract public function query(
        array $search_parameters = [],
        string $order_by = self::ORDER_BY_RELEVANCE,
        int $limit = 200
    ) : array;
}
