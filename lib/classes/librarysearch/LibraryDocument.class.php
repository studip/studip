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
 * This class represents a document from a library.
 */
class LibraryDocument
{
    /**
     * An unique ID of the document.
     */
    public $id = '';


    /**
     * The OPAC ID of the document.
     */
    public $opac_document_id = '';


    /**
     * The link to the document page in the OPAC system.
     */
    public $opac_link = '';


    /**
     * The CSL document type.
     */
    public $type = '';


    /**
     * The CSL datafields.
     */
    public $csl_data = [];


    /**
     * The name of the catalog from which the document has been retrieved.
     */
    public $catalog = '';


    /**
     * Other data that cannot be stored in the $csl_data array.
     */
    public $datafields;


    /**
     * The search parameter that have been used to retrieve this item.
     */
    public $search_params = [];


    /**
     * Generates an ID for the document.
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = md5(uniqid('LibraryDocument' . $this->getTitle()));
        }
        return $this->id;
    }


    /**
     * Returns the type of the document as string.
     *
     * @returns string The type of the document.
     */
    public function getType($format = 'name'): string
    {
        global $LIBRARY_DOCUMENT_TYPES;

        if ($format === 'name') {
            return $this->type;
        }
        if ($format === 'display_name') {
            $ldt = SimpleCollection::createFromArray($LIBRARY_DOCUMENT_TYPES);
            $found = $ldt->findOneBy('name', $this->type);
            $lang = in_array($_SESSION['_language'], ['de_DE', 'en_GB']) ? $_SESSION['_language'] : 'de_DE';
            if ($found) {
                return $found['display_name'][$lang];
            }
        }

        return '';
    }


    /**
     * Returns the title of the document as string.
     *
     * @param string $format The format of the title.
     *     'short' means that only the title is returned.
     *     'long' means that the author, the year and the title are
     *     concatenated into one string.
     *     'long-comma' means that the author, the title and the year
     *     are concatenated in that order, only separated by a comma.
     *
     * @returns string The title of the document.
     */
    public function getTitle($format = 'short'): string
    {
        if ($format == 'long') {
            $long_title = '';
            if ($this->csl_data['issued'] && $this->csl_data['author']) {
                $first_author_last_name = $this->csl_data['author'][0]['family'];
                $year = $this->getIssueDate(true);
                if ($year) {
                    $long_title = sprintf('%1$s (%2$s) - ', $first_author_last_name, $year);
                } else {
                    $long_title = sprintf('%s - ', $first_author_last_name);
                }
            } elseif ($this->csl_data['author']) {
                $first_author_last_name = $this->csl_data['author'][0]['family'];
                $long_title = sprintf('%s - ', $first_author_last_name);
            }
            $long_title .= $this->csl_data['title'];
            return $long_title;
        } elseif ($format == 'long-comma') {
            $data = [];
            $first_author_last_name = trim($this->csl_data['author'][0]['family']);
            $year = trim($this->getIssueDate(true));
            if ($first_author_last_name) {
                $data[] = $first_author_last_name;
            }
            $data[] = $this->csl_data['title'];
            if ($year) {
                $data[] = $year;
            }
            return implode(', ', $data);
        } else {
            return $this->csl_data['title'] ? $this->csl_data['title'] : '';
        }
    }


    /**
     * @returns string A list with all author names.
     */
    public function getAuthorNames(): string
    {
        if (!$this->csl_data['author']) {
            return '';
        }

        $names = [];
        foreach ($this->csl_data['author'] as $author) {
            $names[] = sprintf('%1$s, %2$s', $author['family'], $author['given']);
        }
        return implode('; ', $names);
    }


    /**
     * Returns a string with the issue date.
     *
     * @param bool $year_only Whether to return only the year of the date (true)
     *     or the whole date (false). Defaults to false.
     *
     * @returns string A string representing the issue date.
     */
    public function getIssueDate($year_only = false): string
    {
        if (!$this->csl_data['issued']) {
            return '';
        }
        if ($year_only) {
            $year = @$this->csl_data['issued']['date-parts'][0][0];
            return $year ?: '';
        }
        return implode('-', $this->csl_data['issued']['date-parts'][0]) ?: '';
    }


    /**
     * @returns string A string with identifiers of the document (ISBN, URL, ...).
     */
    public function getIdentifiers(): string
    {
        $identifiers = [];
        if ($this->csl_data['ISBN']) {
            $identifiers[] = sprintf(_('ISBN: %s'), $this->csl_data['ISBN']);
        }
        if ($this->csl_data['ISSN']) {
            $identifiers[] = sprintf(_('ISSN: %s'), $this->csl_data['ISSN']);
        }
        return implode('; ', $identifiers);
    }


    /**
     * Filters the CSL data fields by the document type.
     * Only those CSL fields that are specified in library_config.inc.php for
     * the document type are kept.
     */
    public function filterCslFieldsByType()
    {
        if (!$this->type) {
            return;
        }

        $doc_type_config = null;
        foreach ($GLOBALS['LIBRARY_DOCUMENT_TYPES'] as $doc_config) {
            if ($doc_config['name'] == $this->type) {
                $doc_type_config = $doc_config;
                break;
            }
        }
        if ($doc_type_config == null) {
            return;
        }
        $this->csl_data = array_filter(
            $this->csl_data,
            function ($field) use ($doc_type_config) {
                return in_array($field, $doc_type_config['properties']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }


    /**
     * Converts this LibraryDocument to an associative array.
     *
     * @returns array An associative array containing the data of this
     *     LibraryDocument instance.
     */
    public function toArray(): array
    {
        $data = [
            'id'            => $this->getId(),
            'type'          => $this->getType(),
            'csl_data'      => $this->csl_data,
            'datafields'    => $this->datafields,
            'search_params' => $this->search_params,
            'catalog'       => $this->catalog,
            'opac_link'     => $this->opac_link
        ];
        return $data;
    }


    /**
     * Converts this LibraryDocument to JSON data.
     *
     * @returns string A string containing the JSON encoded version of this
     *     LibraryDocument instance.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }


    /**
     * Fills or creates empty or missing CSL data fields that may be required
     * when rendering the CSL data.
     *
     * @returns array The "enriched" CSL data from this document.
     */
    public function fillEmptyCslFields(): array
    {
        $enriched_data = [];
        foreach ($this->csl_data as $key => $field) {
            if ($key == 'author') {
                if (!$field[0]['family']) {
                    $field[0]['family'] = ' ';
                }
            }
            $enriched_data[$key] = $field;
        }
        //Make sure all "mandatory" fields are there:
        if (!array_key_exists('author', $enriched_data)) {
            $enriched_data['author'] = [
                [
                    'given'  => '',
                    'family' => ' ',
                    'suffix' => ''
                ]
            ];
        }
        return $enriched_data;
    }


    /**
     * Creates a LibraryDocument instance from an associative array.
     *
     * @param array $data An associative array containing data for
     *     a LibraryDocument instance.
     *
     * @returns LibraryDocument|null A LibraryDocument instance on success
     *     or null on failure.
     */
    public static function createFromArray(array $data = [])
    {
        if (!$data) {
            return null;
        }
        $doc = new LibraryDocument();
        $doc->id = $data['id'];
        $doc->type = $data['type'];
        $doc->csl_data = $data['csl_data'];
        $doc->datafields = $data['datafields'];
        $doc->search_params = $data['search_params'];
        $doc->catalog = $data['catalog'];
        $doc->opac_link = $data['opac_link'];
        return $doc;
    }


    /**
     * Creates a LibraryDocument instance from JSON data.
     *
     * @param string $json_string A JSON string containing data for
     *     a LibraryDocument instance.
     *
     * @returns LibraryDocument|null A LibraryDocument instance on success
     *     or null on failure.
     */
    public static function createFromJson(string $json_string = "")
    {
        if (!$json_string) {
            return null;
        }
        $data = json_decode($json_string);
        if (!$data) {
            return null;
        }
        return self::createFromArray($data);
    }


    /**
     * Determines if this document is equal to another document.
     * Equality is determined by comparing various ID fields
     * and as a last resort, the title, author and year are compared.
     *
     * @param LibraryDocument $other Another library document that shall be
     *     compared to this document.
     *
     * @returns bool True, if this document is equal to the other document,
     *     false otherwise.
     */
    public function isEqualTo(LibraryDocument $other): bool
    {
        if ($this->type != $other->type) {
            //No need to do any further checks.
            return false;
        }
        if ($this->id && ($this->id == $other->id)) {
            return true;
        } elseif ($this->csl_data['ISSN'] && ($this->csl_data['ISSN'] == $other->csl_data['ISSN'])) {
            return true;
        } elseif ($this->csl_data['ISBN'] && ($this->csl_data['ISBN'] == $other->csl_data['ISBN'])) {
            return true;
        } elseif ($this->csl_data['DOI'] && ($this->csl_data['DOI'] == $other->csl_data['DOI'])) {
            return true;
        } elseif ($this->csl_data['title'] && $this->csl_data['author'] && $this->csl_data['issued']
            && ($this->csl_data['title'] == $other->csl_data['title'])
            && ($this->csl_data['author'] == $other->csl_data['author'])
            && ($this->csl_data['issued'] == $other->csl_data['issued'])) {
            return true;
        }
        return false;
    }


    /**
     * @returns Flexi_Template A template containing information about the
     *     the document.
     */
    public function getInfoTemplate($format = 'short')
    {
        $factory = new Flexi_TemplateFactory(
            $GLOBALS['STUDIP_BASE_PATH'] . '/templates/library/'
        );
        $template = $factory->open('library_document_info');
        $template->set_attribute('document', $this);
        $template->set_attribute('format', $format);
        return $template;
    }


    /**
     * Creates a descriptive text of the search parameters that lead to the
     * retrieval of this document.
     *
     * @returns string[] An array with a textual representation of all
     *     used search parameters.
     */
    public function getSearchDescription()
    {
        $description = [];
        if ($this->search_params[LibrarySearch::AUTHOR]) {
            $description[] = sprintf(_('Autor: „%s“'), $this->search_params[LibrarySearch::AUTHOR]);
        }
        if ($this->search_params[LibrarySearch::YEAR]) {
            $description[] = sprintf(_('Jahr: „%s“'), $this->search_params[LibrarySearch::YEAR]);
        }
        if ($this->search_params[LibrarySearch::TITLE]) {
            $description[] = sprintf(_('Titel: „%s“'), $this->search_params[LibrarySearch::TITLE]);
        }
        if ($this->search_params[LibrarySearch::NUMBER]) {
            $description[] = sprintf(_('Nummer: „%s“'), $this->search_params[LibrarySearch::NUMBER]);
        }
        if ($this->search_params[LibrarySearch::PUBLICATION]) {
            $description[] = sprintf(_('Zeitschrift: „%s“'), $this->search_params[LibrarySearch::PUBLICATION]);
        }
        if ($this->search_params[LibrarySearch::SIGNATURE]) {
            $description[] = sprintf(_('Signatur: „%s“'), $this->search_params[LibrarySearch::SIGNATURE]);
        }
        return $description;
    }

    public function getIcon()
    {
        global $LIBRARY_DOCUMENT_TYPES;
        $ldt = SimpleCollection::createFromArray($LIBRARY_DOCUMENT_TYPES);
        $found = $ldt->findOneBy('name', $this->type);
        if ($found) {
            $shape = $found['icon'];
        }
        $shape = $shape ?: 'literature-request';
        return Icon::create($shape);
    }
}
