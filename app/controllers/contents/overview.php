<?php

class Contents_OverviewController extends AuthenticatedController
{
    /**
     * Entry point of the controller that displays the dashboard page of Stud.IP.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Mein Arbeitsplatz'));
        Navigation::activateItem('/contents/overview/index');
        Helpbar::Get()->addPlainText(
            _('Alle Inhalte an einem Ort.'),
            _('Sie finden in diesem neuen Bereich Zugriff auf Ihre eigenen Inhalte: Courseware Lernmodule, Dateien und freie Lernmaterialien (OER), die Sie auch mit anderen Standorten austauschen können. Ihre Lehrveranstaltungen finden Sie am gewohnten Platz im Veranstalungsbereich.'),
            Icon::create('info')
        );
        $this->tiles = Navigation::getItem('/contents');
    }
}
