<?php
/**
 * quicksearch.php - trails-controller for delivering search-suggestions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Controller for the ajax-response of the QuickSearch class found in
 * lib/classes/QuickSearch.class.php
 */
class QuicksearchController extends AuthenticatedController
{
    /**
     * the one action which is called by the QuickSearch-form when typed in
     * by user.
     *
     * @param string query_id first argument of url -> id of query in session
     */
    public function response_action($query_id)
    {
        try {
            $needle    = Request::get('request');
            $form_data = iterator_to_array(Request::getInstance());

            $this->search = QuickSearch::getFromSession($query_id);
            $results      = $this->getResults($needle, $form_data);

            $this->render_json($results);
        } catch (Exception $e) {
            $this->set_status(500);
            $this->render_text($e->getMessage());
        }
    }

    /**
     * Highlights the given needle in given subject with the given format.
     *
     * @param String $needle  Search for this string...
     * @param String $subject ...inside this string...
     * @param String $format  ...and replace it with this string (regexp
     *                        syntax)
     *
     * @return String containing the subject with highlighted needle
     */
    private function highlight($needle, $subject, $format = '<b>$0</b>')
    {
        $needle  = htmlReady($needle);
        $subject = htmlReady($subject);
        $regexp  = '/' . preg_quote($needle, '/') . '/i';

        return preg_replace($regexp, $format, $subject);
    }

    /**
     * private method to get a result-array in the way of array(array(item_id, item-name)).
     *
     * @param String $needle   The search keyword typed by the user.
     * @param array $form_data Additonal form data
     *
     * @return array of matched records
     */
    private function getResults($needle, $form_data)
    {
        if (!$this->search instanceof SearchType) {
            throw new Exception(_('Session abgelaufen oder unbekannter Suchtyp'));
        }

        $limit = 10;
        if ($this->search instanceof StandardSearch && $this->search->search === 'username') {
            $limit = 50;
        }

        $results = $this->search->getResults($needle, $form_data, $limit);

        $output = [];
        foreach ($results as $result) {
            $formatted = [
                'item_id'          => $result[0],
                'item_name'        => $this->highlight($needle, $result[1]),
                'item_description' => '',
                'item_search_name' => $result[1],
            ];

            if ($this->search instanceof StandardSearch && $this->search->extendedLayout) {
                $formatted['item_name'] = $this->search->getAvatarImageTag($result[0], Avatar::MEDIUM, ['title' => '']) . $formatted['item_name'];
                if($result[3]) {
                    $formatted['item_description'] = sprintf('%s (%s)', $result[2], $result[3]);
                } else {
                    $formatted['item_description'] = $result[2];
                }
            } else if ($this->search instanceof SearchType) {
                 $formatted['item_name'] = $this->search->getAvatarImageTag($result[0], Avatar::SMALL, ['title' => '']) . $formatted['item_name'];
            }
            $output[] = $formatted;
        }
        return $output;
    }
}
