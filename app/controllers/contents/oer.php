<?php

class Contents_OerController extends AuthenticatedController
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

        PageLayout::setTitle(_('OER'));

        $this->user = $GLOBALS['user'];
    }

    public function index_action($action = false, $widgetId = null)
    {
        Navigation::activateItem('/contents/oer');
        $this->redirect(URLHelper::getURL('dispatch.php/oer/market'));
    }
}