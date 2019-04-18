<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Fachabschluss_FaecherController extends MVVController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem($this->me . '/fachabschluss/faecher');
        $this->action = $action;
    }

    /**
     * Shows all Faecher
     */
    public function index_action()
    {
        $this->initPageParams();

        // Nur Fächer mit verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $filter = ['mvv_fach_inst.institut_id' => MvvPerm::getOwnInstitutes()];
    
        $this->count = Fach::getCount($filter);
        
        if ($this->count < self::$items_per_page * ($this->page - 1)) {
            $this->page = 1;
        }
    
        PageLayout::setTitle(
            _('Fächer mit verwendeten Abschlüssen')
            . ' ('
            . sprintf(ngettext('%s Fach', '%s Fächer', $this->count), $this->count)
            . ')'
        );
        
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        //get data
        $this->faecher = Fach::getAllEnriched(
            $this->sortby,
            $this->order,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1),
            $filter
        );
        if (!isset($this->fach_id)) {
            $this->fach_id = null;
        }
        if (count($this->faecher) === 0) {
            PageLayout::postInfo(_('Es wurden noch keine Fächer angelegt.'));
        }

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie Fächer verwalten und neue Fächer anlegen.').'</br>'));
        $widget->addElement(new WidgetElement(_('Ein Fach kann aufgeklappt werden, um die Abschlüsse anzuzeigen, die dem Fach bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    /**
     * Shows details of LV-Gruppe
     */
    public function details_action($fach_id = null)
    {
        $this->fach = Fach::get($fach_id);
        $this->fach_id = $this->fach->id;
        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected Fach
     */
    public function fach_action($fach_id = null)
    {
        $this->fach = Fach::get($fach_id);
        if ($this->fach->isNew()) {
            PageLayout::setTitle(_('Neues Fach anlegen'));
            $success_message = _('Das Fach "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(sprintf(_('Fach: %s bearbeiten'), $this->fach->getDisplayName()));
            $success_message = _('Das Fach "%s" wurde geändert.');
        }
        //save changes
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->fach->name = Request::i18n('name')->trim();
            $this->fach->name_kurz = Request::i18n('name_kurz')->trim();
            $this->fach->beschreibung = Request::i18n('beschreibung')->trim();
            $this->fach->schlagworte = trim(Request::get('schlagworte'));
            $this->fach->assignFachbereiche(Request::optionArray('institut_items'));
            try {
                $stored = $this->fach->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                PageLayout::postSuccess(sprintf(
                    $success_message,
                    htmlReady($this->fach->name)
                ));
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        // Einrichtung
        $search = new StandardSearch('Institut_id');
        $this->qs_search_id = md5(serialize($search));
        $this->search_institutes_id = md5(serialize($search));
        $this->search_institutes = QuickSearch::get(
            'institut_id',
            new StandardSearch('Institut_id')
        )->fireJSFunctionOnSelect('MVV.Search.addSelected')->noSelectbox();
        
        if (!$this->fach->isNew() && MvvPerm::havePermCreate($this->fach)) {
            $this->setSidebar();
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieses Faches'),
                $this->url_for('shared/log_event/show/Fach/' . $this->fach->getId()),
                Icon::create('log')
            )->asDialog();
        }
    }

    /**
     * Deletes a Fach
     */
    public function delete_action($fach_id)
    {
        $fach = Fach::get($fach_id);
        if (Request::submitted('delete')) {
            CSRFProtection::verifyUnsafeRequest();
            if ($fach->isNew()) {
                PageLayout::postError(_('Das Fach kann nicht gelöscht werden (unbekanntes Fach).'));
            } else {
                $name = $fach->name;
                $fach->delete();
                PageLayout::postSuccess(sprintf(
                    _('Das Fach "%s" wurde gelöscht.'),
                    htmlReady($name)
                ));
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * Shows all Faecher grouped by Fachbereich
     */
    public function fachbereiche_action()
    {
        $filter = ['mvv_fach_inst.institut_id' => MvvPerm::getOwnInstitutes()];
    
        $this->initPageParams('fachbereiche');
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $this->fachbereiche = Fach::getAllFachbereiche(
            $this->sortby,
            $this->order,
            $filter
        );
        PageLayout::setTitle(_('Fächer nach Fachbereichen gruppiert'));

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Ein Fachbereich kann aufgeklappt werden, um die Fächer anzuzeigen, die dem Fachbereich bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    /**
     * Shows all Faecher of a selected Fachbereich
     */
    public function details_fachbereich_action($fachbereich_id = null)
    {
        $this->faecher = Fach::findByFachbereich($fachbereich_id);
        $this->fachbereich_id = $fachbereich_id;
        if (!Request::isXhr()) {
            $this->perform_relayed('fachbereiche');
            return;
        }
    }

    public function abschluss_action($abschluss_id = null)
    {
        $response = $this->relay('fachabschluss/abschluesse/abschluss/' . $abschluss_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            if ($response->headers['Location']) {
                $this->redirect($response->headers['Location']);
            } else {
              $this->relocate('fachabschluss/abschluesse/abschluss/' . $abschluss_id);
            }
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        $widget  = new ViewsWidget();
        $widget->addLink(
            _('Fächer mit zugeordneten Abschlüssen'),
            $this->url_for('/index')
        )->setActive(in_array($this->action, ['index', 'details']));
        $widget->addLink(
            _('Gruppierung nach Fachbereichen'),
            $this->url_for('/fachbereiche')
        )->setActive(in_array($this->action, ['fachbereiche', 'details_fachbereich']));

        $sidebar->addWidget($widget);

        if (MvvPerm::havePermCreate('Fach')) {
            $widget  = new ActionsWidget();
            $widget->addLink(
                _('Neues Fach anlegen'),
                $this->url_for('/fach'),
                Icon::create('file+add')
            );
            $sidebar->addWidget($widget);
        }
        $this->sidebar_rendered = true;
    }
}
