<?
# Lifter002: TODO
# Lifter010: TODO
/**
 * page_intros.inc.php
 *
 * library for the messages on the pages, contents of the sidebar and stuff
 * to display
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright   2003-2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
*/

if ($_SESSION['resources_data']['actual_object']) {
    // tlx: WTF is this chunk of code supposed to do?
    // Lines 31-34 are absolutely useless, aren't they?
    $currentObject = ResourceObject::Factory($_SESSION['resources_data']['actual_object']);
    $currentObjectTitelAdd = ': ' . ($currentObject->getCategoryName() ?: _('Hierachieebene'));
    if ($currentObjectTitelAdd) {
        $currentObjectTitelAdd = ': ';
    }
    $currentObjectTitelAdd = ': ' . $currentObject->getName() . ' (' . $currentObject->getOwnerName() . ')';
}

$helpbar = Helpbar::get();
$sidebar = Sidebar::get();

switch ($view) {
    //Reiter "Uebersicht"
    case 'plan':
        PageLayout::setTitle(_('Spezielle Funktionen'));
    break;
    case 'regular':
        PageLayout::setTitle(_('Spezielle Funktionen'));
    break;
    case 'diff':
        PageLayout::setTitle(_('Spezielle Funktionen'));
    break;

    case 'resources':
        PageLayout::setTitle(_('Übersicht der Ressourcen'));
        Navigation::activateItem('/resources/view/hierarchy');
    break;
    case 'search':
        PageLayout::setTitle(_('Suche nach Ressourcen'));
        Navigation::activateItem('/search/resources');

        $widget = new OptionsWidget();
        $widget->setTitle(_('Suchoptionen'));
        $widget->addCheckbox(_('Eigenschaften anzeigen'),
                             $_SESSION['resources_data']['search_mode'] === 'properties',
                             URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&mode=properties'),
                             URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&mode=browse'));
        // Show only if viewing all room occupations is allowed.
        if (hasGlobalOccupationAccess() ||
                ResourcesUserRoomsList::getInstance($GLOBALS['user']->id, false, false)->numberOfRooms()) {
            $widget->addCheckbox(_('Belegungszeit anzeigen'),
                $_SESSION['resources_data']['check_assigns'],
                URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&check_assigns=TRUE'),
                URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&check_assigns=FALSE'));
        }
        $widget->addCheckbox(_('Nur Räume anzeigen'),
                             $_SESSION['resources_data']['search_only_rooms'],
                             URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&search_only_rooms=1'),
                             URLHelper::getLink('?view=search&quick_view_mode=' . $view_mode . '&search_only_rooms=0'));

        $sidebar->addWidget($widget);
    break;
    //Reiter "Listen"
    case 'lists':
        PageLayout::setTitle(_('Bearbeiten und ausgeben von Listen'));
        Navigation::activateItem('/resources/lists/show');

        if ($_SESSION['resources_data']['list_open']) {
            // tlx: What is this line good for?
            $title.=" - "._("Ebene").": ".getResourceObjectName($_SESSION['resources_data']["list_open"]);

            $helpbar->setVariables([
                'name' => getResourceObjectName($_SESSION['resources_data']['list_open'])
            ]);

            $widget = new OptionsWidget();
            $widget->addCheckbox(_('Untergeordnete Ebenen ausgeben'),
                                 $_SESSION['resources_data']['list_recurse'],
                                 URLHelper::getLink('?recurse_list=TRUE'),
                                 URLHelper::getLink('?nrecurse_list=TRUE'));
            $sidebar->addWidget($widget);
        }
    break;

    //Reiter "Objekt"
    case 'objects':
    case 'edit_object_assign':
        PageLayout::setTitle(_("Belegungen anzeigen/bearbeiten").$currentObjectTitelAdd);
        Navigation::activateItem('/resources/objects/edit_assign');

        if ($view_mode === 'no_nav') {
            $navigation = new Navigation(_('Zurück zum Belegungsplan'), '?quick_view=view_schedule&quick_view_mode=' . $view_mode);
            Navigation::getItem('/resources/objects')->addSubNavigation('search', $navigation);
        } else {
            $page_intro = '<h2>' . sprintf(_('Raum: %s'), $currentObject->getName()) . '</h2>';

            if ($ActualObjectPerms->havePerm('autor') && $currentObject->getCategoryId()) {
                $qv = $view_mode === 'oobj'
                    ? 'openobject_assign'
                    : 'edit_object_assign';

                $widget = new ActionsWidget();
                $widget->addLink(_('Neue Belegung erstellen'),
                                 URLHelper::getURL('?cancel_edit_assign=1&quick_view=' . $qv . '&quick_view_mode=' . $view_mode), Icon::create('date+add', 'clickable'));
                $sidebar->addWidget($widget);
            }

            $navigation = new Navigation(_('Zur Ressourcensuche'), 'resources.php?view=search&quick_view_mode=' . $view_mode);
            Navigation::getItem('/resources/objects')->addSubNavigation('search', $navigation);
        }
    break;
    case 'edit_object_properties':
        PageLayout::setTitle(_('Eigenschaften bearbeiten') . $currentObjectTitelAdd);
        Navigation::activateItem('/resources/objects/edit_properties');
    break;
    case 'edit_object_perms':
        PageLayout::setTitle(_('Rechte bearbeiten') . $currentObjectTitelAdd);
        Navigation::activateItem('/resources/objects/edit_perms');
    break;
    case 'view_schedule':
        PageLayout::setTitle(_('Belegungszeiten ausgeben') . $currentObjectTitelAdd);
        Navigation::activateItem('/resources/objects/view_schedule');

        $page_intro = '<h2>' . sprintf(_('Raum: %s'), $currentObject->getName()) . '</h2>';

        $widget = new ViewsWidget();
        $widget->addLink(_('Eigenschaften anzeigen'),
                         URLHelper::getURL('?quick_view=view_details&quick_view_mode=' . $view_mode), Icon::create('resources', 'clickable'));
        if (Config::get()->RESOURCES_ENABLE_SEM_SCHEDULE) {
            $widget->addLink(_('Semesterplan anzeigen'),
                             URLHelper::getURL('?quick_view=view_sem_schedule&quick_view_mode=' . $view_mode), Icon::create('schedule', 'clickable'));
        }
        $sidebar->addWidget($widget);

        if ($ActualObjectPerms->havePerm('autor') && $currentObject->getCategoryId()) {
            $qv = $view_mode === 'oobj'
                ? 'openobject_assign'
                : 'edit_object_assign';

            $widget = new ActionsWidget();
            $widget->addLink(_('Neue Belegung erstellen'),
                             URLHelper::getURL('?cancel_edit_assign=1&quick_view=' . $qv . '&quick_view_mode=' . $view_mode), Icon::create('date+add', 'clickable'));
            $sidebar->addWidget($widget);
        }

        if ($view_mode !== 'no_nav') {
            if (Context::isCourse()) {
                $navigation = new Navigation(_('Zurück zur Veranstaltung'), 'seminar_main.php');
                Navigation::getItem('/resources/objects')->addSubNavigation('back', $navigation);
            }
            if (Context::isInstitute()) {
                $navigation = new Navigation(_('Zurück zur Einrichtung'), 'dispatch.php/institute/overview');
                Navigation::getItem('/resources/objects')->addSubNavigation('back', $navigation);
            }
        }

        $navigation = new Navigation(_('Zur Ressourcensuche'), 'resources.php?view=search&quick_view_mode=' . $view_mode);
        Navigation::getItem('/resources/objects')->addSubNavigation('search', $navigation);

        $widget = new ExportWidget();
        $widget->addLink(_('Druckansicht'),
                         URLHelper::getURL('?view=view_schedule&print_view=1'), Icon::create('print', 'clickable'),
                         ['target' => '_blank']);
        $sidebar->addWidget($widget);
    break;
    case 'view_sem_schedule':
        PageLayout::setTitle(_('Belegungszeiten pro Semester ausgeben') . $currentObjectTitelAdd);
        Navigation::activateItem('/resources/objects/view_sem_schedule');

        $page_intro = '<h2>' . sprintf(_('Raum: %s'), $currentObject->getName()) . '</h2>';

        $widget = new ViewsWidget();
        $widget->addLink(_('Eigenschaften anzeigen'),
                         URLHelper::getURL('?quick_view=view_details&quick_view_mode=' . $view_mode), Icon::create('resources', 'clickable'));
        if ($view_mode === 'no_nav') {
            $qv = $view_mode === 'oobj'
                ? 'openobject_schedule'
                : 'view_schedule';

            $widget->addLink(_('Belegungsplan anzeigen'),
                             URLHelper::getURL('?quick_view=' . $qv . '&quick_view_mode=no_nav'), Icon::create('schedule', 'clickable'));
        }
        $sidebar->addWidget($widget);

        if ($ActualObjectPerms->havePerm('autor') && $currentObject->getCategoryId()) {
            $qv = $view_mode === 'oobj'
                ? 'openobject_assign'
                : 'edit_object_assign';

            $widget = new ActionsWidget();
            $widget->addLink(_('Neue Belegung erstellen'),
                             URLHelper::getURL('?cancel_edit_assign=1&quick_view=' . $qv . '&quick_view_mode=' . $view_mode), Icon::create('date+add', 'clickable'));
            $sidebar->addWidget($widget);
        }

        if ($view_mode !== 'no_nav') {
            if (Context::isCourse()) {
                $navigation = new Navigation(_('Zurück zur Veranstaltung'), 'seminar_main.php');
                Navigation::getItem('/resources/objects')->addSubNavigation('back', $navigation);
            }
            if (Context::isInstitute()) {
                $navigation = new Navigation(_('Zurück zur Einrichtung'), 'dispatch.php/institute/overview');
                Navigation::getItem('/resources/objects')->addSubNavigation('back', $navigation);
            }
        }

        $navigation = new Navigation(_('Zur Ressourcensuche'), 'resources.php?view=search&quick_view_mode=' . $view_mode);
        Navigation::getItem('/resources/objects')->addSubNavigation('search', $navigation);

        $widget = new ExportWidget();
        $widget->addLink(_('Druckansicht'),
                         URLHelper::getURL('?view=view_sem_schedule&print_view=1'), Icon::create('print', 'clickable'),
                         ['target' => '_blank']);
        $sidebar->addWidget($widget);
    break;
    case 'view_group_schedule':
        $room_groups = RoomGroups::GetInstance();
        PageLayout::setTitle(_('Belegungszeiten einer Raumgruppe pro Semester ausgeben:') . ' ' . $room_groups->getGroupName($_SESSION['resources_data']['actual_room_group']));
        Navigation::activateItem('/resources/view/group_schedule');

        $widget = new ExportWidget();
        $widget->addLink(_('Druckansicht'),
                         URLHelper::getURL('?view=view_group_schedule&print_view=1'), Icon::create('print', 'clickable'),
                         ['target' => '_blank']);
        $sidebar->addWidget($widget);
    break;
    case 'view_group_schedule_daily':
        $room_groups = RoomGroups::GetInstance();
        PageLayout::setTitle(_('Belegungszeiten einer Raumgruppe pro Tag ausgeben:') . ' ' . $room_groups->getGroupName($_SESSION['resources_data']['actual_room_group']));
        Navigation::activateItem('/resources/view/group_schedule_daily');

        $widget = new ExportWidget();
        $widget->addLink(_('Druckansicht'),
                         URLHelper::getURL('?view=view_group_schedule_daily&print_view=1'), Icon::create('print', 'clickable'),
                         ['target' => '_blank']);
        $sidebar->addWidget($widget);
    break;
    //Reiter "Anpassen"
    case 'settings':
    case 'edit_types':
        PageLayout::setTitle(_('Typen bearbeiten'));
        Navigation::activateItem('/resources/settings/edit_types');
    break;
    case 'edit_properties':
        PageLayout::setTitle(_('Eigenschaften bearbeiten'));
        Navigation::activateItem('/resources/settings/edit_properties');
    break;
    case 'edit_perms':
        PageLayout::setTitle(_('globale Rechte der Ressourcenadministratoren bearbeiten'));
        Navigation::activateItem('/resources/settings/edit_perms');
    break;
    case 'edit_settings':
        PageLayout::setTitle(_('Einstellungen der Ressourcenverwaltung'));
        Navigation::activateItem('/resources/settings/edit_settings');
    break;

    //Reiter Raumplanung
    case 'requests_start':
        PageLayout::setTitle(_('Übersicht des Raumplanungs-Status'));
        Navigation::activateItem('/resources/room_requests/start');
    break;
    case 'edit_request':
        PageLayout::setTitle(_('Bearbeiten der Anfragen'));
        Navigation::activateItem('/resources/room_requests/edit');

        $widget = new ActionsWidget();
        $widget->addLink(_('Ressourcen suchen'),
                         URLHelper::getURL('resources.php?view=search&quick_view_mode=no_nav'), Icon::create('search', 'clickable'),
                         ['onclick' => "window.open(this.href, '', 'scrollbars=yes,left=10,top=10,width=1000,height=680,resizable=yes');return false;"]);
        $widget->addLink(_('Nachrichten zu zugewiesenen Anfragen versenden'),
                         URLHelper::getURL('?snd_closed_request_sms=TRUE'), Icon::create('mail', 'clickable'));
        $sidebar->addWidget($widget);

        $widget = new OptionsWidget();
        $widget->addCheckbox(_('Bearbeitete Anfragen anzeigen'),
                             $_SESSION['resources_data']['skip_closed_requests'],
                             URLHelper::getLink('?skip_closed_requests=TRUE'),
                             URLHelper::getLink('?skip_closed_requests=FALSE'));

    break;
    case 'list_requests':
        $helpbar->setVariables([
            'link' => URLHelper::getLink('resources.php?view=requests_start&cancel_edit_request_x=1'),
        ]);
        PageLayout::setTitle(_('Anfragenliste'));
        Navigation::activateItem('/resources/room_requests/list');
    break;
    //all the intros in an open object (Veranstaltung, Einrichtung)
    case 'openobject_main':
        $identifier = $perm->have_studip_perm('autor', Context::getId())
            ? 'resources/openobject_main_priviledged'
            : 'resources/openobject_main';
        $helpbar->setVariables([
            'type'        => Context::getTypeName(),
            'member_type' => Context::isCourse() ? _('Teilnehmende') : _('Mitarbeiter/-in'),
        ]);

        PageLayout::setTitle(Context::getHeaderLine()." - "._("Ressourcenübersicht"));
        Navigation::activateItem('/course/resources/overview');
    break;
    case 'openobject_details':
    case 'view_details':
        if ($view_mode === 'oobj') {
            PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Ressourcendetails') . $currentObjectTitelAdd);
            Navigation::activateItem('/course/resources/view_details');
        } else {
            PageLayout::setTitle(_('Anzeige der Ressourceneigenschaften') . $currentObjectTitelAdd);
            Navigation::activateItem('/resources/objects/view_details');
        }

        if ($view_mode == 'no_nav' && is_object($currentObject) && $currentObject->getCategoryId()) {
            $widget = new ViewsWidget();
            $widget->addLink(_('Belegungsplan anzeigen'),
                             URLHelper::getURL('?quick_view=view_schedule&quick_view_mode=no_nav'), Icon::create('schedule', 'clickable'));
            $sidebar->addWidget($widget);

            if ($ActualObjectPerms->havePerm('autor')) {
                $widget = new ActionsWidget();
                $widget->addLink(_('Neue Belegung erstellen'),
                                 URLHelper::getURL('?cancel_edit_assign=1&quick_view=edit_object_assign&quick_view_mode=' . $view_mode), Icon::create('date+add', 'info'));
                $sidebar->addWidget($widget);
            }
        }

    break;

    case 'openobject_schedule':
        if ($_SESSION['resources_data']['actual_object']) {
            $helpbar->setVariables([
                'name'     => $currentObject->getName(),
                'category' => $currentObject->getCategoryName(),
            ]);
        }

        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Ressourcenbelegung'));
        Navigation::activateItem('/course/resources/view_schedule');
    break;
    case 'openobject_assign':
        if ($_SESSION['resources_data']['actual_object']) {
            $helpbar->setVariables([
                'name'     => $currentObject->getName(),
                'category' => $currentObject->getCategoryName(),
            ]);
        }
        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . ('Belegung anzeigen/bearbeiten'));
        Navigation::activateItem('/course/resources/edit_assign');
    break;
    case 'openobject_group_schedule':
        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Belegungszeiten aller Ressourcen pro Tag ausgeben'));
        Navigation::activateItem('/course/resources/group_schedule');

        $widget = new ExportWidget();
        $widget->addLink(_('Druckansicht'),
                         URLHelper::getURL('?view=openobject_group_schedule&print_view=1'), Icon::create('print', 'clickable'),
                         ['target' => '_blank']);
        $sidebar->addWidget($widget);
    break;
    case 'view_requests_schedule':
        PageLayout::setTitle(_('Anfragenübersicht eines Raums:') . ' ' . ResourceObject::Factory($_SESSION['resources_data']['resolve_requests_one_res'])->getName());
        Navigation::activateItem('/resources/room_requests/schedule');

        $widget = new ViewsWidget();
        $widget->addLink(_('Semesterplan'),
                         URLHelper::getURL('resources.php?actual_object=' . $_SESSION['resources_data']['resolve_requests_one_res'] . '&quick_view=view_sem_schedule&quick_view_mode=no_nav'),
                         Icon::create('schedule', 'clickable'),
                         ['onclick' => "window.open(this.href, '', 'scrollbars=yes,left=10,top=10,width=1000,height=680,resizable=yes');return false;"]);
        $sidebar->addWidget($widget);
    break;
    //default
    default:
        PageLayout::setTitle(_('Übersicht der Ressourcen'));
        Navigation::activateItem('/resources/view/hierarchy');
    break;
}

//general naming of resources management pages
if (!Context::get()) {
    PageLayout::setTitle(_('Ressourcenverwaltung:') . ' ' . PageLayout::getTitle());
}
