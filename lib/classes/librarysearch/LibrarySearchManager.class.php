<?php


/**
 * This is a helper class for the library search functionality.
 */
class LibrarySearchManager
{
    /**
     * Checks whether catalogs are configured or not.
     *
     * @param bool Whether to exclude local catalogs from the list of
     *     configured catalogs (true) or not (false). Defaults to false.
     *
     * @returns bool True, if at least one catalog is configured,
     *     false otherwise.
     */
    public static function catalogsConfigured(bool $exclude_local = false) : bool
    {
        $activated_catalogs = $GLOBALS['LIBRARY_CATALOGS'];
        if (!$activated_catalogs) {
            return false;
        }

        if (!$exclude_local) {
            //At least one catalog is configured.
            return true;
        }

        //Check if there are other catalogs than the local one:
        $other_catalogs = 0;
        foreach ($activated_catalogs as $catalog) {
            if (!$catalog['local_catalog']) {
                $other_catalogs++;
            }
        }

        return $other_catalogs > 0;
    }


    /**
     * Starts a search in the configured library catalogs.
     * If a local catalog is configured, its results are compared to
     * the other catalogs results to identify matches that are available
     * in the local catalog.
     *
     * @param array $search_parameters The search parameters to be used.
     *     @see The LibrarySearch class for standardised field names.
     *
     * @param string $order_by The ordering of the search results.
     *     @see The LibrarySearch class for allowed order names.
     *
     * @param int $limit The maximum amount of results for each catalog.
     *
     * @returns LibraryDocument[][] A two-dimensional array of
     *     LibraryDocument instances where the first dimension represents
     *     a catalog and the second dimension represents the search results.
     *
     * @throws Exception If no library catalogs are configured.
     */
    public static function search(
        array $search_parameters = [],
        string $order_by = LibrarySearch::ORDER_BY_RELEVANCE,
        int $limit = 100
    ) : array
    {
        //Get the set of activated library catalogs:
        $activated_catalogs = $GLOBALS['LIBRARY_CATALOGS'];
        if (!$activated_catalogs) {
            throw new Exception(
                _('In dieser Stud.IP-Installation sind keine Bibliothekskataloge aktiviert!')
            );
        }

        $result_sets = [];
        $local_catalog_result_set = [];
        foreach ($activated_catalogs as $catalog_data) {
            if (is_a($catalog_data['class_name'], 'LibrarySearch', true)) {
                $catalog_class = $catalog_data['class_name'];
                $catalog_config = [
                    'base_url' => $catalog_data['base_url'],
                    'additional_url_parameters' => $catalog_data['additional_url_parameters'],
                    'settings' => $catalog_data['settings']
                ];
                $search = new $catalog_class($catalog_config);
                if ($catalog_data['local_catalog']) {
                    $local_catalog_result_set = $search->query(
                        $search_parameters,
                        $order_by,
                        $limit
                    );
                    foreach ($local_catalog_result_set as $result) {
                        $result->catalog = $catalog_data['name'];
                        if ($result->csl_data['id']) {
                            $result->opac_document_id = $result->csl_data['id'];
                            if (isset($catalog_data['opac_link_template'])) {
                                $result->opac_link = str_replace(
                                    '{opac_document_id}',
                                    htmlReady($result->opac_document_id),
                                    $catalog_data['opac_link_template']
                                );
                            }
                        }
                    }
                } else {
                    $result_sets[$catalog_class] = $search->query(
                        $search_parameters,
                        $order_by,
                        $limit
                    );
                    foreach ($result_sets[$catalog_class] as $result) {
                        $result->catalog = $catalog_data['name'];
                    }
                }
            }
        }

        //Build the sorted result set by rotating over each unsorted
        //result set until the end of each result set is reached.
        //This way, we get the top results for each catalog as first
        //entries in the result set.
        //Furthermore, filter out all entries that are already present
        //in the local catalog.
        $all_empty = false;
        $result_c = 0;
        $merged_results = [];
        $iterators = [];
        foreach ($result_sets as $set) {
            $iterators[] = new ArrayIterator($set);
        }
        if (count($local_catalog_result_set)) {
            foreach ($local_catalog_result_set as $result) {
                $result->search_params = $search_parameters;
                $merged_results[$result->getId()] = $result;
            }
        }
        while (!$all_empty) {
            $all_empty = true;
            foreach ($iterators as $iterator) {
                $result = $iterator->current();
                if ($result instanceof LibraryDocument) {
                    $result_c++;
                    $all_empty = false;
                    $found_in_local_catalog = false;
                    foreach ($local_catalog_result_set as $key => $local_result) {
                        if ($local_result->isEqualTo($result)) {
                            //The result is in the local catalog.
                            unset($local_catalog_result_set[$key]);
                            $found_in_local_catalog = true;
                            break;
                        }
                    }
                    if (!$found_in_local_catalog) {
                        //Store the result in the cache.
                        //We need it in the create_library action.
                        //Put the search parameters into the result before adding it
                        //to the cache:
                        $result->search_params = $search_parameters;
                        $merged_results[$result->getId()] = $result;
                    }
                }
                if ($iterator->valid()) {
                    $all_empty = false;
                    $iterator->next();
                }
            }
        }


        //At this point, the search results are sorted.
        return $merged_results;
    }
}
