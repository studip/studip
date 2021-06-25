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
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index_action()
    {
        Navigation::activateItem('/contents/overview/index');

        $this->tiles = Navigation::getItem('/contents');
    }
}
