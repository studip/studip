<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Materialien_DokumenteController extends MVVController
{
    public $filter = [];
    private $show_sidebar_search = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    
        $this->filter = $this->sessGet('filter', []);
    
        Navigation::activateItem($this->me . '/materialien/dokumente');
        $this->action = $action;
    }

    public function index_action()
    {
        $this->initPageParams();
        $this->initSearchParams();
        
        $search_result = $this->getSearchResult('MvvDokument');
    
        $this->filter = array_merge(
            ['mvv_dokument.dokument_id' => $search_result],
            (array)$this->filter
        );
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        $this->dokumente = MvvDokument::getAllEnriched(
            $this->sortby,
            $this->order,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1),
            $this->filter
        );

        if (!count($this->dokumente)) {
            PageLayout::postInfo(sprintf(
                _('Es wurden noch keine Dokumente angelegt. Klicken Sie %shier%s, um ein neues Dokument anzulegen.'),
                '<a href="'
                . $this->url_for('/dokument') . '">',
                '</a>')
            );
        }
        if (!isset($this->dokument_id)) {
            $this->dokument_id = null;
        }
        $this->count = MvvDokument::getCount($this->filter);
        $this->show_sidebar_search = true;
        $this->setSidebar();
    
        PageLayout::setTitle(
            _('Verlinkte Materialien/Dokumente')
            . ' ('
            . sprintf(ngettext('%s Dokument', '%s Dokumente', $this->count), $this->count)
            . ')'
        );
    
    }

    public function details_action($dokument_id = null)
    {
        $this->dokument = MvvDokument::find($dokument_id);
        if (!$this->dokument) {
            throw new Trails_Exception(404);
        }
        $this->dokument_id = $this->dokument->id;
        $this->relations = $this->dokument->getRelations();
        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected document
     */
    public function dokument_action($dokument_id = null)
    {
        $this->dokument = MvvDokument::get($dokument_id);
        if ($this->dokument->isNew()) {
            PageLayout::setTitle(_('Neues Dokument anlegen'));
            $success_message = _('Das Dokument <em>%s</em> wurde angelegt.');
        } else {
            PageLayout::setTitle(sprintf(
                _('Dokument: %s bearbeiten'),
                htmlReady($this->dokument->getDisplayName())
            ));
            $success_message = _('Das Dokument "%s" wurde geändert.');
        }
        $success = false;
        //save changes
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->dokument->url = trim(Request::get('url'));
            $this->dokument->name = Request::i18n('name')->trim();
            $this->dokument->linktext = Request::i18n('linktext')->trim();
            $this->dokument->beschreibung = Request::i18n('beschreibung')->trim();
            try {
                $stored = $this->dokument->store();
            } catch (InvalidValuesException $e) {
                Pagelayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->reset_search;
                $success = true;
                if (!Request::isXhr()) {
                    if ($stored) {
                        PageLayout::postSuccess(sprintf(
                            $success_message,
                            htmlReady($this->dokument->name)
                        ));
                    } else {
                        PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                    }
                    $this->redirect($this->url_for('/index'));
                }
            }
        }
        $this->cancel_url = $this->url_for('/index');
        if (Request::isXhr()) {
            if ($success) {
                $ret = [
                    'func'    => "MVV.Content.addItemFromDialog",
                    'payload' => [
                        'target'    => 'dokumente',
                        'item_id'   => $this->dokument->id,
                        'item_name' => $this->dokument->getDisplayName()
                    ]
                ];
                $this->response->add_header('X-Dialog-Close', 1);
                $this->response->add_header('X-Dialog-Execute', json_encode($ret));
                $this->render_nothing();
                return;
            }
        }

        $this->setSidebar();
        if (!$this->dokument->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieses Dokumentes'),
                $this->url_for('shared/log_event/show/Dokument/' . $this->dokument->id),
                Icon::create('log')
            )->asDialog();
        }
    }

    /**
     * Deletes a document
     */
    public function delete_action($dokument_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $dokument = MvvDokument::get($dokument_id);
        if ($dokument->isNew()) {
            PageLayout::postError(_('Das Dokument kann nicht gelöscht werden (unbekanntes Dokument).'));
        } else {
            CSRFProtection::verifyUnsafeRequest();
            $name = $dokument->name;
            $dokument->delete();
            PageLayout::postSuccess(sprintf(
                _('Das Dokument "%s" wurde gelöscht.'),
                htmlReady($name)
            ));
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * do the search
     */
    public function search_action()
    {
        if (Request::get('reset-search')) {
            $this->reset_search('dokumente');
            $this->reset_page();
        } else {
            $this->reset_search('dokumente');
            $this->reset_page();
            $this->do_search(
                'MvvDokument',
                trim(Request::get('dokument_suche_parameter')),
                Request::get('dokument_suche'), $this->filter
            );
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('MvvDokument');
        $this->perform_relayed('index');
    }

    /**
     * sets filter parameters and store these in the session
     */
    public function set_filter_action()
    {
        $this->filter = [];
        
        // filtered by object type (Zuordnungen)
        $this->filter['mvv_dokument_zuord.object_type']
                = mb_strlen(Request::get('zuordnung_filter'))
                ? Request::option('zuordnung_filter') : null;
        $this->sessSet('filter', $this->filter);
        $this->reset_page();
        $this->redirect($this->url_for('/index'));
    }

    public function reset_filter_action()
    {
        $this->filter = [];
        $this->sessRemove('filter');
        $this->perform_relayed('index');
    }

    public function ref_properties_action($dokument_id, $object_id, $object_type)
    {
        $dokument = MvvDokument::find($dokument_id);
        if ($dokument) {
            $this->relation = MvvDokumentZuord::findOneBySQL(
                '`dokument_id` = ? AND `range_id` = ? AND `object_type` = ?',
                [$object_id, $object_type, $dokument_id]
            );
            if (!$this->relation) {
                $this->relation = new MvvDokumentZuord();
                $this->relation->dokument_id = $dokument_id;
                $this->relation->range_id = $object_id;
                $this->relation->object_type = $object_type;
            }
        } else {
            $this->render_nothing();
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));
        
        $widget  = new ActionsWidget();
        if (MvvPerm::get('MvvDokument')->havePermCreate()) {
            $widget->addLink(
                _('Neues Dokument anlegen'),
                $this->url_for('/dokument'),
                Icon::create('file+add')
            );
        }
        $sidebar->addWidget($widget);

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
            $this->sidebar_filter();
        }
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf dieser Seite können Sie Dokumente verwalten, die mit Studiengängen, Studiengangteilen usw. verknüpft sind.')));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }

    /**
     * adds the search funtion to the sidebar
     */
    private function sidebar_search()
    {
        $query = 'SELECT dokument_id, name
                FROM mvv_dokument
                LEFT JOIN mvv_dokument_zuord USING(dokument_id)
                WHERE (name LIKE :input
                OR url LIKE :input) '
                . ModuleManagementModel::getFilterSql($this->filter);
        $search_term = $this->search_term ? $this->search_term : _('Dokument suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('/search'));
        $widget->addNeedle(
            _('Dokument suchen'),
            'dokument_suche',
            true,
            new SQLSearch($query, $search_term, 'dokument_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $sidebar->addWidget($widget, 'search');
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $template_factory = $this->get_template_factory();
        $filter_template = $template_factory->render('shared/filter',
            [
                'zuordnungen'        => MvvDokument::getAllRelations($this->search_result['MvvDokument']),
                'selected_zuordnung' => $this->filter['mvv_dokument_zuord.object_type'],
                'action'             => $this->url_for('/set_filter'),
                'action_reset'       => $this->url_for('/reset_filter')
            ]);
    
        $sidebar = Sidebar::get();
        $widget  = new SidebarWidget();
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement($filter_template));
        $sidebar->addWidget($widget,"filter");
    }

    public function dispatch_action($class_name, $id)
    {
        switch (mb_strtolower($class_name)) {
            case 'fach':
                $this->redirect('fachabschluss/faecher/fach/' . $id);
                break;
            case 'abschlusskategorie':
                $this->redirect('fachabschluss/kategorien/kategorie/' . $id);
                break;
            case 'abschluss':
                $this->redirect('fachabschluss/abschluesse/abschluss/' . $id);
                break;
            case 'studiengangteil':
                $this->redirect('studiengaenge/studiengangteile/stgteil/' . $id);
                break;
            case 'studiengang':
                $this->redirect('studiengaenge/studiengaenge/studiengang/' . $id);
                break;
            case 'stgteilversion':
                $version = StgteilVersion::get($id);
                if ($version->isNew()) {
                    $this->flash_set('error', _('Unbekannte Version'));
                    $this->redirect('studiengaenge/studiengaenge');
                }
                $this->redirect(
                    'studiengaenge/studiengangteile/version/' . join('/', [$version->stgteil_id, $version->getId()])
                );
                break;
            default:
                $this->redirect('studiengaenge/studiengaenge/');
        }
    }
}
