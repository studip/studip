<?php
/**
 * This class provides a generic search widget for the sidebar.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @since     3.1
 * @license   GPL2 or any later version
 * @copyright Stud.IP Core Group
 */
class SearchWidget extends SidebarWidget
{
    const INDEX = 'search';

    protected $url;
    protected $needles = [];
    protected $filters = [];
    protected $method = 'get';
    protected $id = null;

    /**
     * Constructor for the widget.
     *
     * @param String $url URL to send the search to
     */
    public function __construct($url = '')
    {
        parent::__construct();

        $this->url      = $url ?: $_SERVER['REQUEST_URI'];
        $this->title    = _('Suche');
        $this->template = 'sidebar/search-widget';
    }

    /**
     * Sets the id for the form element.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the id for the form element.
     *
     * @return mixed String containing the id or null if no id has been set.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the request method used for the form.
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns the request method used for the form.
     *
     * @return string containing the chosen request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add a needle to search (optionally as quick search)
     *
     * @param String $label        Label for the input element
     * @param String $name         Name of the input (which will be the
     *                             transmitted name attribute)
     * @param bool   $placeholder  Use label as placeholder (this will hide
     *                             the associated label)
     * @param mixed  $quick_search An optional SearchType object if quick
     *                             search should be used
     * @param mixed  $js_func      Optional name of a js function or a js
     *                             function itself that's executed when an
     *                             entry of the found elements is selected
     */
    public function addNeedle($label, $name, $placeholder = false, SearchType $quick_search = null, $js_func = null, $value = null)
    {
        $value = $value ?: Request::get($name);
        $this->needles[] = compact(['label', 'name', 'placeholder', 'value', 'quick_search',  'js_func']);
    }

    /**
     * Add a filter option. This will create a checkbox with the given key
     * as the name attribute.
     *
     * @param String $label Label of the filter
     * @param String $key   Key/name of the filter (this will be the name
     *                      attribute)
     */
    public function addFilter($label, $key)
    {
        $this->filters[$key] = $label;
    }

    /**
     * Returns whether the widgets has any elements. Since this widget uses
     * a special template, not all elements are "real" SidebarElements.
     *
     * @return bool If widget has any element.
     */
    public function hasElements()
    {
        return count($this->elements) + count($this->needles) + count($this->filters) > 0;
    }

    /**
     * Renders the widget.
     *
     * @param Array $variables Unused variables parameter
     * @return String containing the html output of the widget
     */
    public function render($variables = [])
    {
        $query = parse_url($this->url, PHP_URL_QUERY);
        if ($query) {
            $this->url = str_replace('?' . $query, '', $this->url);
            parse_str(html_entity_decode($query) ?: '', $query_params);
        } else {
            $query_params = [];
        }

        $this->template_variables['url']        = URLHelper::getLink($this->url);
        $this->template_variables['url_params'] = $query_params;

        $this->template_variables['method'] = $this->method;
        $this->template_variables['id']     = $this->id;

        foreach ($this->needles as $index => $needle) {
            if ($needle['quick_search']) {
                $quick_search = QuickSearch::get($needle['name'], $needle['quick_search']);
                if ($needle['js_func'] !== null) {
                    $quick_search->fireJSFunctionOnSelect($needle['js_func']);
                }

                $needle['quick_search'] = $quick_search;
                $this->needles[$index] = $needle;
            }
        }

        if ($this->hasData()) {
            $reset_link = sprintf(
                '<a href="%s">%s %s</a>',
                URLHelper::getLink($this->url, array_merge($query_params, ['reset-search' => 1])),
                Icon::create('search+decline')->asImg(['class' => 'text-top']),
                _('Zurücksetzen')
            );
            $this->template_variables['reset_search'] = $reset_link;
        }

        $this->template_variables['needles'] = $this->needles;
        $this->template_variables['filters'] = $this->filters;

        $this->template_variables['has_data'] = $this->hasData();

        return parent::render($variables);
    }

    /**
     * Returns whether the widget has any data.
     * The widget has data if it was submitted and any of the needles
     * or needles has been filled out.
     *
     * @return bool indicating whether the request method matches and any
     *              element has data.
     */
    protected function hasData()
    {
        if (Request::method() !== mb_strtoupper($this->method)) {
            return false;
        }

        foreach ($this->needles as $needle) {
            if ($needle['value']) {
                return true;
            }
        }

        foreach (array_keys($this->filters) as $key) {
            if (Request::submitted($key) && !Request::int($key)) {
                return true;
            }
        }

        return false;
    }

}
