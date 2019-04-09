<?php
/**
 *
 * This class contains methods to generate links to LonCapa
 *
 * @modulegroup  elearning_interface_modules
 * @module       LonCapaConnectedLink
 * @package  ELearning-Interface
 */
class LonCapaConnectedLink extends ConnectedLink
{
    /**
     * get user module links
     *
     * returns content module links for user
     * @return string html-code
     */
    public function getUserModuleLinks()
    {
        global $connected_cms, $current_module;

        $url = URLHelper::getURL('dispatch.php/loncapa/enter', ['cms_type' => $this->cms_type, 'module' => $current_module]);

        return Studip\LinkButton::create(_('Starten'), $url, [
            'target' => '_blank',
            'rel'    => 'noopener noreferrer',
        ]);
    }

    /**
     * get admin module links
     *
     * returns links add or remove a module from course
     * @return string returns html-code
     */
    public function getAdminModuleLinks()
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;
        global $template_factory;

        $template = $template_factory->open('elearning/loncapa_connected_link_edit');
        $template->current_module = $connected_cms[$this->cms_type]->content_module[$current_module]->getId();
        $template->connected = $connected_cms[$this->cms_type]->content_module[$current_module]->isConnected();
        $template->cms_type = $this->cms_type;
        $template->search_key = $search_key;
        return $template->render(compact('view', 'search_key', 'cms_select', 'current_module'));
    }

    /**
     * returns url for connected LonCapa course
     *
     * @param string $module_id LonCapa ID
     * @param string $course_id Stud.IP course ID
     * @return string url for LonCapa
     */
    public function getRedirectUrl($module_id, $course_id)
    {
        return sprintf(
            '%s/enter/%s?token=%s&courseid=%s&systemid=%s',
            $this->cms_link,
            $module_id,
            Token::create(60),
            $course_id,
            $this->cms_type
        );
    }
}
