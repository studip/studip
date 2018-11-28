<?php
/**
 * media_proxy.php - media proxy controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class MediaProxyController extends StudipController
{
    /**
     * Empty before filter to avoid any unwanted output changes.
     *
     * @param string $action
     * @param array  $args
     */
    public function before_filter(&$action, &$args)
    {
    }

    /**
     * default action of this controller: proxy media data
     */
    public function index_action()
    {
        $url = Request::get('url');
        $media_proxy = new MediaProxy();
        $config = Config::GetInstance();
        $modified_since = NULL;

        if (!Seminar_Session::is_current_session_authenticated() ||
            $config->getValue('LOAD_EXTERNAL_MEDIA') != 'proxy') {
            throw new AccessDeniedException();
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }

        ini_set('default_socket_timeout', 5);
        $this->render_nothing();

        //stop output buffering started in Trails_Dispatcher::dispatch()
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $media_proxy->readURL($url, $modified_since);
        } catch (MediaProxyException $ex) {
            header($ex->getMessage());
        }
    }
}
