<?php

class Contents_FilesController extends AuthenticatedController
{
        /**
     * Callback function being called before an action is executed.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Dateien'));

        $this->user = $GLOBALS['user'];
    }

    public function index_action($action = false, $widgetId = null)
    {
        Navigation::activateItem('/contents/files');
        $this->redirect(URLHelper::getURL('dispatch.php/files/overview'));
    }
}