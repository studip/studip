<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

require_once __DIR__ . '/shared_version.php';

class Studiengaenge_StudiengangteileController extends SharedVersionController
{
    protected $show_sidebar_search = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem($this->me . '/studiengaenge/studiengangteile');
        $this->action = $action;
    }
    
    public function index_action()
    {
        $this->initPageParams();
        $this->initSearchParams();

        // Nur Studiengangteile mit zugeordnetem Fach an dessen verantwortlicher
        // Einrichtung der User eine Rolle hat
        $this->filter['mvv_fach_inst.institut_id'] = MvvPerm::getOwnInstitutes();

        $search_result = $this->getSearchResult('StudiengangTeil');
        $filter = $this->filter;
        $this->sortby = $this->sortby ?: 'fach_name,zusatz,kp';
        $this->order = $this->order ?: 'ASC';
        unset($filter['start_sem.beginn'], $filter['end_sem.ende']);
        //get data
        if (count($search_result)) {
            $filter['stgteil_id'] = $search_result;
            $this->stgteile = StudiengangTeil::getAllEnriched(
                $this->sortby, $this->order,
                $filter, self::$items_per_page,
                self::$items_per_page * ($this->page - 1)
            );
            $this->count = count($search_result);
        } else {
            $this->stgteile = StudiengangTeil::getAllEnriched(
                $this->sortby, $this->order,
                $filter, self::$items_per_page,
                self::$items_per_page * ($this->page - 1)
            );
            if (count($this->stgteile) === 0) {
                PageLayout::postInfo(_('Es wurden noch keine Studiengangteile angelegt.'));
            }
            $this->count = StudiengangTeil::getCount($filter);
        }
        PageLayout::setTitle(sprintf(
            _('Verwaltung der Studiengangteile - Alle Studiengangteile (%u)'), $this->count
        ));

        $this->show_sidebar_search = true;
        $this->setSidebar();
    }

    public function stgteil_action($stgteil_id = null)
    {
        if (!isset($this->stgteil)) {
            $this->stgteil = StudiengangTeil::get($stgteil_id);
        }
        
        if ($this->stgteil->isNew()) {
            $this->stgteil->setNewId();
            PageLayout::setTitle(_('Neuen Studiengangteil anlegen'));
            $success_message = ('Der Studiengangteil "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(sprintf(
                _('Studiengangteil: %s bearbeiten'),
                htmlReady($this->stgteil->getDisplayName())
            ));
            $success_message = _('Der Studiengangteil "%s" wurde geändert.');
            if ($this->stgteil->fach) {
                $this->fach_id = $this->stgteil->fach->getId();
            }
        }
        
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            if ($this->stgteil->isNew()) {
                $this->sessDelete();
            }
            $this->stgteil->kp = Request::get('kp');
            $this->stgteil->semester = Request::int('semester');
            $this->stgteil->zusatz = Request::i18n('zusatz')->trim();
            $this->stgteil->assignFach(Request::option('fach_item'));
            $this->stgteil->assignFachberater(Request::optionArray('fachberater_items'));
            try {
                $stored = $this->stgteil->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                if ($stored) {
                    PageLayout::postSuccess(sprintf(
                        $success_message,
                        htmlReady($this->stgteil->getDisplayName())
                    ));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $query = 'SELECT fach_id, name FROM fach
                WHERE name LIKE :input ORDER BY name ASC';
        $search = new SQLSearch($query, _('Fach suchen'), 'fach_id');
        $this->search_fach_id = md5(serialize($search));
        $this->search_fach = QuickSearch::get('fach', $search)
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();

        $query = "
            SELECT
            user_id,
            CONCAT(Vorname, ' ', Nachname, ' (', username, ')') AS name
            FROM auth_user_md5
            WHERE Nachname LIKE :input
            OR username LIKE :input
            AND perms IN('autor', 'tutor', 'dozent', 'admin')";
        $search = new SQLSearch($query, _('Studienfachberater suchen'));
        $this->search_fachberater_id = md5(serialize($search));
        $this->search_fachberater =
                QuickSearch::get('fachberater', $search)
                ->fireJSFunctionOnSelect('MVV.Search.addSelected')
                ->noSelectbox();
        $this->cancel_url = $this->url_for('/index');

        $this->setSidebar();
        if (!$this->stgteil->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieses Studiengangteils'),
                $this->url_for('shared/log_event/show/StudiengangTeil/' . $this->stgteil->getId()),
                Icon::create('log')
            )->asDialog();
        }

        $this->render_template('studiengaenge/studiengangteile/stgteil', $this->layout);
    }

    public function copy_action($stgteil_id)
    {
        $stgteil_orig = StudiengangTeil::find($stgteil_id);
        if ($stgteil_orig) {
            $this->stgteil = clone $stgteil_orig;
            $this->stgteil->setNewId();
            $this->stgteil->fachberater = $stgteil_orig->fachberater;

        } else {
            throw new Trails_Exception(404);
        }
        $this->perform_relayed('stgteil');
    }
    
    /**
     * Delete Studiengangteil
     * @param $stgteil_id
     */
    public function delete_action($stgteil_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $stgteil        = StudiengangTeil::find($stgteil_id);
        $stg_stgteile   = StudiengangStgteil::findBySql('stgteil_id = ' . DBManager::get()->quote($stgteil->getId()));
        
        if (count($stg_stgteile)) {
            PageLayout::postInfo(_('Der Studiengangteil kann nicht gelöscht werden, da er Studiengängen zugeordnet ist.'));
        } else {
            PageLayout::postSuccess(sprintf(
                _('Studiengangteil "%s" gelöscht!'),
                htmlReady($stgteil->getDisplayName())
            ));
            $stgteil->delete();
            $this->sessDelete();
        }
        
        $this->redirect($this->url_for('/index'));
    }

    public function details_action($stgteil_id)
    {
        $this->stgteil = StudiengangTeil::find($stgteil_id);
        $this->versionen = StgteilVersion::findByStgteil($stgteil_id);

        if (count($this->versionen)) {
            $this->stgteil_id = $stgteil_id;
            if (!Request::isXhr()) {
                $this->perform_relayed('index');
            }
        } else {
            if (Request::isXhr()) {
                $this->set_status(404, 'Not Found');
                $this->render_nothing();
            } else {
                $this->redirect($this->url_for('/index'));
            }
        }
    }

    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('StudiengangTeil');
            $this->reset_page();
        } else {
            // Nur Studiengangteile mit zugeordnetem Fach an dessen verantwortlicher
            // Einrichtung der User eine Rolle hat
            $perm_institutes = MvvPerm::getOwnInstitutes();
            $filter = [];
            if (count($perm_institutes)) {
                $filter['mvv_fach_inst.institut_id'] = $perm_institutes;
            }
            $this->reset_search('StudiengangTeil');
            $this->reset_page();
            $this->do_search('StudiengangTeil',
                trim(Request::get('stgteil_suche_parameter')),
                Request::option('stgteil_suche'), $filter);
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('StudiengangTeil');
        $this->reset_page();
        $this->redirect($this->url_for('/index'));
    }

    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        $widget = new ViewsWidget();
        $widget->addLink(
            _('Liste der Studiengangteile'),
            $this->url_for('studiengaenge/studiengangteile')
        )->setActive(get_called_class() == 'Studiengaenge_StudiengangteileController');
        $widget->addLink(
            _('Gruppiert nach Fächern'),
            $this->url_for('studiengaenge/faecher')
        )->setActive(get_called_class() == 'Studiengaenge_FaecherController');
        $widget->addLink(
            _('Gruppiert nach Fachbereichen'),
            $this->url_for('studiengaenge/fachbereichestgteile')
        )->setActive(get_called_class() == 'Studiengaenge_FachbereichestgteileController');
        $sidebar->addWidget($widget);

        $widget = new ActionsWidget();
        if (MvvPerm::havePermCreate('StudiengangTeil')) {
            $widget->addLink(
                _('Neuen Studiengangteil anlegen'),
                $this->url_for('/stgteil'),
                Icon::create('file+add')
            );
        }
        $sidebar->addWidget($widget);

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
        }

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie die Studiengangteile verwalten. Ein Fach kann einen oder mehrere Studiengangteil(e) haben, die Studiengängen zugeordnet werden.') . '</br>'));
        $widget->addElement(new WidgetElement(_("Studiengangteile können nur gelöscht werden, wenn Sie keinem Studiengang zugeordnet sind.")));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }

    /**
     * adds the search function to the sidebar
     */
    private function sidebar_search()
    {
        $query = "SELECT ms.stgteil_id,
                    IF(ISNULL(ms.kp), CONCAT(mf.name, ' ', ms.zusatz),
                    CONCAT(mf.name, ' ', ms.zusatz, ' (', ms.kp, ' CP', ')')) AS name
                    FROM mvv_stgteil ms
                    LEFT JOIN fach mf USING(fach_id)
                    WHERE ms.zusatz LIKE :input
                    OR mf.name LIKE :input";
        
        $search_term = $this->search_term ? $this->search_term : _('Studiengangteil suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(
            _('Studiengangteil suchen'),
            'stgteil_suche',
            true,
            new SQLSearch($query, $search_term, 'studiengang_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $widget->setTitle('Suche');
        $sidebar->addWidget($widget, 'search');
    }
}
