<?php
class ForumController extends StudipController {
    var $lastlogin = 0;

    // customized #url_for for plugins
    function url_for($to = '')
    {
        $args = func_get_args();

        # find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->current_plugin, $params, join('/', $args));
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * */
    /* * * * * H E L P E R   F U N C T I O N S * * * * */
    /* * * * * * * * * * * * * * * * * * * * * * * * * */
    function getId()
    {
        return ForumHelpers::getSeminarId();
    }

    /**
     * Common code for all actions: set default layout and page title.
     *
     * @param type $action
     * @param type $args
     */
    function before_filter(&$action, &$args)
    {
        $this->validate_args($args, ['option', 'option']);

        parent::before_filter($action, $args);

        $this->flash = Trails_Flash::instance();

        // Set help keyword for Stud.IP's user-documentation and page title
        PageLayout::setHelpKeyword('Basis.Forum');
        PageLayout::setTitle(Context::getHeaderLine() .' - '. _('Forum'));

        $this->AVAILABLE_DESIGNS = ['web20', 'studip'];
        if ($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] && $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] != '/') {
            $this->picturepath = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] .'/'. $this->dispatcher->trails_root . '/img';
        } else {
            $this->picturepath = '/'. $this->dispatcher->trails_root . '/img';
        }

        // the default for displaying timestamps
        $this->time_format_string = "%a %d. %B %Y, %H:%M";
        $this->time_format_string_short = "%d.%m.%Y, %H:%M";

        $this->template_factory =
            new Flexi_TemplateFactory(dirname(__FILE__) . '/../templates');

        //$this->check_token();

        //$this->getId() depends on Context::get()
        checkObject();
        ForumVisit::setVisit($this->getId());
        if (Request::int('page')) {
            ForumHelpers::setPage(Request::int('page'));
        }

        $this->seminar_id = $this->getId();
    }
}
