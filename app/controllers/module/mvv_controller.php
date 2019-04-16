<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class MVVController extends AuthenticatedController
{
    /**
     * The maximum number of items listed on a page.
     * If the list is longer pagination is displayed.
     *
     * @var int
     */
    public static $items_per_page;

    /**
     * Array of ids of mvv object found by search action.
     *
     * @var array
     */
    public $search_result = [];

    /**
     * Holds the last search term.
     *
     * @var string
     */
    public $search_term = '';

    /**
     * Holds the last id of an mvv object selected in quick search.
     *
     * @var string
     */
    public $search_id = null;

    /**
     * TRUE if sidebar is already rendered.
     *
     * @var bool
     */
    protected $sidebar_rendered = false;

    /**
     * The key of an index name used to store values in the session.
     * It is the top level key of an multidimensional array that holds all
     * values stored in the session by the current controller.
     * One part of the key is the name of the controller.
     *
     * @var string
     */
    protected $session_key;


    /**
     * The second level key of the array that holds values in the session from
     * current controller. It is derived from the name of the current
     * action normally.
     *
     * @var string
     */
    public $param_suffix = '';

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!static::IsVisible()) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Module'));

        // Setup flash instance
        $this->flash = Trails_Flash::instance();

        $this->me             = 'mvv';
        self::$items_per_page = Config::get()->getValue('ENTRIES_PER_PAGE');

        $this->session_key = $this->me . '_' . mb_substr(get_class($this), 0, -10);
    }

    /**
     * Returns a controller based (considers name of action if given)
     * suffix for url parameters.
     *
     * @param string $action The name of the action (optional).
     * @return string Suffix for parameters.
     */
    public function paramSuffix($action = '')
    {
        $param_suffix = mb_strtolower(preg_filter(
            ['/^.*_/', '/Controller$/'], '', get_called_class(), 1));
        return $action ? '_' . $param_suffix . '_' . $action : '_' . $param_suffix;
    }

    /**
     * Initialzes the controller (considers name of action if given) based
     * parameters for search and bind them to url.
     *
     * @param string $action The name of the action (optional).
     */
    protected function initSearchParams($action = '')
    {
        $this->search_params_suffix = $this->paramSuffix($action);

        $this->search_term = Request::get('search_term' . $this->search_params_suffix, $this->sessGet('search_term'));

        URLHelper::bindLinkParam('search_term' . $this->search_params_suffix, $this->search_term);

        $this->search_id = Request::option('search_id' . $this->search_params_suffix, $this->sessGet('search_id'));

        URLHelper::bindLinkParam('search_id' . $this->search_params_suffix, $this->search_id);
    }

    /**
     * Initialzes the controller (considers name of action if given) based
     * parameters for page navigation and bind them to url.
     *
     * @param string $action The name of the action (optional).
     */
    protected function initPageParams($action = null)
    {
        $this->page_params_suffix = $this->paramSuffix($action);
        $action = $action ? '_' . $action : '';
        URLHelper::bindLinkParam('page' . $this->page_params_suffix, $this->page);
        $this->page = Request::int(
            'page' . $this->page_params_suffix,
            $this->sessGet('page' . $this->page_params_suffix)
        );
        $this->page = intval($this->page) > 1 ? $this->page : 1;
        $this->sessSet('page' . $this->page_params_suffix, $this->page);
        URLHelper::bindLinkParam('sortby' . $this->page_params_suffix, $this->sortby);
        $this->sortby = Request::get(
            'sortby' . $this->page_params_suffix,
            $this->sessGet('sortby' . $this->page_params_suffix)
        );
        $this->sessSet('sortby' . $this->page_params_suffix, $this->sortby);
        URLHelper::bindLinkParam('order' . $this->page_params_suffix, $this->order);
        $this->order = Request::get(
            'order' . $this->page_params_suffix,
            $this->sessGet('order' . $this->page_params_suffix)
        );
        $this->sessSet('order' . $this->page_params_suffix, $this->order);
    }

    /**
     * Determines the visibility of this controller.
     *
     * @return bool True if the controller is visible.
     */
    protected static function IsVisible()
    {
        return MVV::isVisible();
    }

    /**
     * Renders a html snippet with a sort link used in table headers.
     *
     * @param string $action The action called by this link.
     * @param string $text The text of the link.
     * @param string $field The sort to sort by.
     * @param array $attributes Additional url attributes.
     * @return string The html snippet.
     */
    public function renderSortLink($action, $text, $field, $attributes = null)
    {
        $template = $this->get_template_factory()->open('shared/sort_link');
        $template->set_attributes(
            [
                'controller' => $this,
                'action'     => $action,
                'text'       => $text,
                'field'      => $field,
                'attributes' => (array)$attributes
            ]);
        return $template->render();
    }

    /**
     * Sets the sidebar with all widgets and set value of sidebar_rendered
     * to true.
     *
     */
    protected function setSidebar()
    {
        $this->sidebar_rendered = true;
        $sidebar                = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');
    }

    /**
     * Renders a html snippet containing an url. This url is used by
     * java script.
     *
     * @param string $to A string containing a controller and optionally
     * an action. Default is the current controller.
     * @param array $params An array with url parameters.
     * @return string The html used in templates
     */
    public function jsUrl($to = '', $params = [])
    {
        if ($to === '') {
            $to = str_replace('_', '/', mb_substr(mb_strtolower(get_class($this)),
                    0, -10)) . '/';
        }
        $to = $this->url_for($to);
        list($url, $query) = explode('?', $to);
        $url      = URLHelper::getUrl($url, $params, true);
        $template = $this->get_template_factory()->open('shared/js_url');
        $template->set_attributes(['url' => $url]);
        return $template->render();
    }

    /**
     * This action is used to show a select box instead of an input field
     * if the user has clicked on the magnifier icon of a quicksearch.
     *
     * @throws Trails_Exception
     */
    public function qs_result_action()
    {
        if (Request::isPost()) {
            $this->render_json(self::getQsResult(
                Request::option('qs_id'),
                Request::get('qs_term')
            ));
        } else {
            throw new Trails_Exception(404);
        }
    }

    /**
     * Retrieves the result set of quicksearch to show a select box.
     *
     * @param string $qs_id The id of the quicksearch.
     * @param string $qs_term The search term.
     * @return null|array The result set.
     */
    private static function getQsResult($qs_id, $qs_term)
    {
        $search = self::getSearch($qs_id);
        if ($search) {
            $results[] = [
                'id'   => '',
                'name' => '-- ' . _('Bitte wählen') . ' --',
            ];
            foreach ($search->getResults($qs_term) as $result) {
                $results[] = [
                    'id'   => $result[0],
                    'name' => $result[1]
                ];
            }
            return $results;
        }
        return null;
    }

    /**
     * Retrieves a quick search sql object from session by its id
     * (md5 of serialized object).
     *
     * @param string $qs_id The quick search id of the search object.
     * @return object A search object.
     */
    private static function getSearch($qs_id)
    {
        $search = null;
        if ($qs_id) {
            try {
                $search = unserialize($_SESSION['QuickSearches'][$qs_id]['object']);
            } catch (Exception $e) {
                return null;
            }
        }
        return is_object($search) ? $search : null;
    }

    /**
     * Perform the search for mvv objects of type defined by $class_name.
     * Uses the findBySearchTerm method with its parameters $search_term and
     * $filter. If $search_id is given, only this item will be found.
     * Sets info messages with number of hits to page layout.
     *
     * @see ModuleManagementModel::findBySearchTerm()
     * @param string $class_name The name of an mvv object class.
     * @param string $search_term The search term.
     * @param string $search_id The id of an mvv object selected in quicksearch.
     * @param array $filter An array with filter options feeded to search
     * function to restrict search result.
     */
    protected function do_search($class_name, $search_term = null, $search_id = null, $filter = null)
    {
        if (!count($this->search_result)) {
            $search_id = $search_id ?: $this->search_id;
            if ($search_id) {
                $found_object = $class_name::find($search_id);
                if ($found_object) {
                    $this->search_result = [$found_object->getId()];
                    $this->search_term   = $found_object->getDisplayName();
                    if (!$this->search_id) {
                        PageLayout::postInfo(sprintf(
                            _('"%s" ausgewählt.'),
                            htmlReady($found_object->getDisplayName())
                        ));
                    }
                    $this->search_id = $search_id;
                    $this->sessSet('search_id', $this->search_id);
                }
            } else {
                $search_term = $search_term ?: $this->search_term;
                $filter      = $filter ?: $this->filter;
                if ($search_term) {
                    $this->search_result =
                        $class_name::findBySearchTerm($search_term, $filter)->pluck('id');
                    if ($this->current_action === 'search') {
                        if (count($this->search_result)) {
                            PageLayout::postInfo(sprintf(
                                _('%s Treffer für die Suche nach "%s".'),
                                count($this->search_result),
                                htmlReady($search_term)
                            ));
                            $this->search_term = $search_term;
                        } else {
                            PageLayout::postInfo(sprintf(
                                _('Keine Treffer für die Suche nach "%s".'),
                                htmlReady($search_term)
                            ));
                        }
                        unset($this->search_id);
                        $this->sessRemove('search_id');
                    }
                }
            }
        }
        $this->sessSet('search_term', $this->search_term);
    }

    /**
     * Returns the current search result of the given class. The search result
     * is an array of object ids.
     *
     * @param string The class name of the found objects.
     * @return array Array of search results.
     */
    protected function getSearchResult($class_name)
    {
        $this->do_search($class_name);

        return $this->search_result;
    }

    /**
     * Deletes the search results stored in $this->search_result for the
     * given action.
     *
     * @param string $action The name of the action that uses the
     * particular search.
     */
    protected function reset_search($action = '')
    {
        $this->search_params_suffix = $this->paramSuffix($action);

        // reset search
        $this->search_result = [];
        unset($this->search_term);
        URLHelper::removeLinkParam('search_term' . $this->search_params_suffix);
        $this->sessRemove('search_term');
        unset($this->search_id);
        URLHelper::removeLinkParam('search_id' . $this->search_params_suffix);
        $this->sessRemove('search_id');
    }

    /**
     * Resets the main page parameters for pagination and sorting for the given
     * action.
     *
     * @param string $action The name of the action that uses the pagination and
     * sorting.
     */
    protected function reset_page($action = '')
    {
        $this->page_params_suffix = $this->paramSuffix($action);

        // reset page chooser
        $this->page = 1;
        $this->sessRemove('page' . $this->page_params_suffix);
        URLHelper::removeLinkParam('page' . $this->page_params_suffix);

        // reset sorting
        $this->sortby = '';
        $this->sessRemove('sortby' . $this->page_params_suffix);
        URLHelper::removeLinkParam('sortby' . $this->page_params_suffix);
        $this->order = 'DESC';
        $this->sessRemove('order' . $this->page_params_suffix);
        URLHelper::removeLinkParam('order' . $this->page_params_suffix);
    }

    /**
     * Stores a value with the given key in the session.
     *
     * @param string $key The key of the value.
     * @param mixed $value The value to store under the given key.
     * @return the stored value
     */
    protected function sessSet($key, $value)
    {
        $_SESSION[$this->session_key][$key] = $value;
        return $value;
    }

    /**
     * Returns the value of the given key from the session.
     *
     * @param string $key The key of the value to return.
     * @return mixed The value from session with the given key.
     */
    protected function sessGet($key, $default = null)
    {
        return (isset($_SESSION[$this->session_key][$key])
            ? $_SESSION[$this->session_key][$key]
            : $default);
    }

    /**
     * Removes the value with the given key from the session.
     *
     * @param string|array $keys The key of the value to remove from session.
     */
    protected function sessRemove($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $key) {
            unset($_SESSION[$this->session_key][$key]);
        }
    }

    /**
     * Deletes all values from the session used in this controller.
     *
     */
    protected function sessDelete()
    {
        unset($_SESSION[$this->session_key]);
    }


    /**
     * This weird WYSIWIG-Editor stores an empty string as an empty diff-element.
     * Use this function to check whether the field has no content
     * (no input by the user).
     *
     * @param string $text The text from db to check.
     * @return string An empty string, if the content is an empty diff
     * or an empty string
     */
    public static function trim($text)
    {
        $text = trim($text);
        return preg_match('%\<div.*?\>\</div\>%', $text) ? '' : $text;
    }
}
