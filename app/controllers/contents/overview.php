<?php

class Contents_OverviewController extends AuthenticatedController
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

        PageLayout::setTitle(_('Inhalte'));

        $this->user = $GLOBALS['user'];
    }

        /**
     * Entry point of the controller that displays the dashboard page of Stud.IP.
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index_action($action = false, $widgetId = null)
    {
        global $perm;

        if (Navigation::hasItem("/contents/overview/index")) {
            Navigation::activateItem("/contents/overview/index");
        }

        $this->show_oer_item = Config::get()->OERCAMPUS_ENABLED && $perm && $perm->have_perm(Config::get()->OER_PUBLIC_STATUS);
    }
}
