<?php
/**
 * Help section
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since 3.1
 */
class Helpbar extends WidgetContainer
{
    protected $open = false;
    protected $should_render = true;
    protected $variables = [];
    protected $ignore_db = false;

    /**
     * Constructs the helpbar
     */
    public function __construct()
    {
        parent::__construct();

        $this->help_admin = isset($GLOBALS['perm']) && ($GLOBALS['perm']->have_perm('root') || RolePersistence::isAssignedRole($GLOBALS['user']->id, 'Hilfe-Administrator(in)'));
    }

    /**
     * load help content from db
     */
    public function loadContent()
    {
        $route        = get_route();
        $help_content = HelpContent::getContentByRoute();
        foreach ($help_content as $row) {
            $this->addPlainText($row['label'] ?: '',
                                $this->interpolate($row['content'], $this->variables),
                                $row['icon'] ? Icon::create($row['icon'], 'info_alt') : null,
                                URLHelper::getURL('dispatch.php/help_content/edit/'.$row['content_id'], ['from' => $route]),
                                URLHelper::getURL('dispatch.php/help_content/delete/'.$row['content_id'], ['from' => $route]));
        }
        if (!count($help_content) && $this->help_admin) {
            $this->addPlainText('',
                                '',
                                null,
                                null,
                                null,
                                URLHelper::getURL('dispatch.php/help_content/add', ['?help_content_route' => $route, 'from' => $route]));
        }
    }

    /**
     * set variables for help content
     *
     * @param Array $variables The variables to set
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;
    }

    /**
     * Interpolates a string with variables.
     *
     * Essentially, the string "i am #{name}" with the variables
     * ['name' => 'groot'] will be converted to "i am groot". I guess
     * you get the principle.
     *
     * @param mixed $string    String to interpolate (an array of string
     *                         may be passed as well)
     * @param array $variables Variables to interpolate into the string(s)
     * @return mixed Either an interpolated string or an array of such
     */
    protected function interpolate($string, $variables = [])
    {
        if (is_array($string)) {
            return array_map([$this, 'interpolate'], $string, array_pad([], count($string), $variables));
        }

        $replaces = [];
        foreach ($variables as $needle => $replace)
        {
            $replaces['#{' . $needle . '}'] = $replace;
        }
        return str_replace(array_keys($replaces), array_values($replaces), $string);
    }

    /**
     * Adds text entries to the helpbar.
     *
     * @param String $label       Label/category
     * @param String $text        The text item itself
     * @param mixed  $icon        An optional, additional icon
     * @param mixed  $edit_link   Optional edit link if the user may do so
     * @param mixed  $delete_link Optional delete link if the user may do so
     * @param mixed  $add_link    Optional add link if the user may do so
     */
    public function addPlainText($label, $text, $icon = null, $edit_link = null, $delete_link = null, $add_link = null)
    {
        if (is_array($text)) {
            $first = array_shift($text);
            $this->addPlainText($label, $first, $icon);

            foreach ($text as $item) {
                $this->addPlainText('', $item);
            }

            return;
        }

        if ($label) {
            $content = sprintf('<strong>%s</strong><p>%s</p>',
                            htmlReady($label), formatReady($text));
        } else {
            $content = sprintf('<p>%s</p>', formatReady($text));
        }

        if ($icon instanceof \Icon) {
            $icon = $icon->copyWithRole('info_alt');
        }

        $widget = new HelpbarWidget();
        $widget->setIcon($icon);
        $widget->addElement(new WidgetElement($content));
        if ($this->help_admin) {
            $widget->edit_link = $edit_link;
            $widget->delete_link = $delete_link;
            $widget->add_link = $add_link;
        }
        $this->addWidget($widget);
    }

    /**
     * Adds an entry from the database to the helpbar.
     *
     * @param String $label Label for the entry
     * @param String $id    Id of the entry
     */
    public function addText($label, $id)
    {
        $widget = new HelpbarWidget();
        $widget->addElement(new HelpbarTextElement($label, $id));
        $this->addWidget($widget, 'help-' . $id);
    }

    /**
     * Adds a link to the helpbar
     *
     * @param String $label      Label of the link
     * @param String $url        The link itself
     * @param mixed  $icon       An optional, additional icon
     * @param mixed  $target     The target attribute of the link element
     * @param array  $attributes Additional attribute for the link element
     */
    public function addLink($label, $url, $icon = false, $target = false, $attributes = [])
    {
        $id = md5($url);

        $element = new LinkElement($label, $url);
        $element->attributes = $attributes;
        $element->setTarget($target);

        $widget = new HelpbarWidget();
        $widget->addElement($element);
        $widget->setIcon($icon);

        $this->addWidget($widget, 'help-' . $id);
    }

    /**
     * Inserts a link to the helpbar before all other elements
     *
     * @param String $label      Label of the link
     * @param String $url        The link itself
     * @param mixed  $icon       An optional, additional icon
     * @param mixed  $target     The target attribute of the link element
     * @param array  $attributes Additional attribute for the link element
     */
    public function insertLink($label, $url, $icon = false, $target = false, $attributes = [])
    {
        $id = md5($url);

        $element = new LinkElement($label, $url);
        $element->attributes = $attributes;
        $element->setTarget($target);

        $widget = new HelpbarWidget();
        $widget->addElement($element);
        $widget->setIcon($icon);

        $this->insertWidget($widget, ':first', 'help-' . $id);
    }

    /**
     * Tells the helpbar whether it should be open by default.
     *
     * @param bool $state Indicating whether the helpbar should be open
     */
    public function open($state = true)
    {
        $this->open = $state;
    }

    /**
     * Tells the helpbar whether it should render.
     *
     * @param bool $state Indicating whether the helpbar should render
     */
    public function shouldRender($state = true)
    {
        $this->should_render = $state;
    }

    /**
     * Tells the helpbar to ignore any potentially stored contents from the
     * database.
     * This is neccessary for pages like the wiki where a helpbar entry is
     * present in the database but since url parameters are currently
     * ignored, the entry would apply for all pages of the wiki - which it
     * shouldn't.
     * This is just a makeshift solution until arbitrary routes can be
     * handled.
     *
     * @param bool $state Indicating whether the contents should be ignored
     * @todo remove this as soon as the helpbar can handle arbitrary routes
     */
    public function ignoreDatabaseContents($state = true)
    {
        $this->ignore_db = $state;
    }

    /**
     * Renders the help bar.
     * The helpbar will only be rendered if it actually contains any widgets.
     * It will use the template "helpbar.php" located at "templates/helpbar".
     * A notification is dispatched before and after the actual rendering
     * process.
     *
     * @return String The HTML code of the rendered helpbar.
     */
    public function render()
    {
        if (!$this->ignore_db) {
            $this->loadContent();
        }

        // add tour links
        if (Config::get()->TOURS_ENABLE) {
            $widget = new HelpbarTourWidget();
            if ($widget->hasElements()) {
                $this->addWidget($widget);
            }
            $tour_data = $widget->tour_data;
        }

        // add wiki link and remove it from navigation
        $this->addLink(
            _('Weiterführende Hilfe'),
            format_help_url(PageLayout::getHelpKeyword()), Icon::create('link-extern', 'info_alt'),
            '_blank',
            ['rel' => 'noopener noreferrer']
        );

        NotificationCenter::postNotification('HelpbarWillRender', $this);

        if ($this->should_render && $this->hasWidgets()) {
            $template = $GLOBALS['template_factory']->open('helpbar/helpbar');
            $template->widgets   = $this->widgets;
            $template->open      = $this->open;
            $template->tour_data = $tour_data;
            $content = $template->render();
        }

        NotificationCenter::postNotification('HelpbarDidRender', $this);

        return $content;
    }
}
