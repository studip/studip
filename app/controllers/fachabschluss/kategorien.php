<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Fachabschluss_KategorienController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/fachabschluss/kategorien');
        $this->action = $action;
    }

    /**
     * Lists all Abschluss-Kategorien
     */
    function index_action()
    {
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Sie können die Reihenfolge der Abschluss-Kategorien durch Ziehen der Zeilen ändern.').'</br>'));
        $widget->addElement(new WidgetElement(_('Eine Abschluss-Katgorie kann aufgeklappt werden, um die Abschlüsse anzuzeigen, die der Abschluss-Kategorie bereits zugeordnet wurden.')));
        $widget->addElement(new WidgetElement(_('Die Reihenfolge der zugeordneten Abschlüsse kann ebenfalls geändert werden.')));
        $helpbar->addWidget($widget);

        // Nur Abschluss-Kategorien mit Abschlüssen, die Studiengängen zugeordnet
        // sind an deren Verantwortliche Einrichtung dem User eine Rolle
        // zugewiesen wurde
        $filter = ['mvv_studiengang.institut_id' => MvvPerm::getOwnInstitutes()];
    
        $this->abschluss_kategorien = AbschlussKategorie::getAllEnriched(
            'position',
            'ASC',
            null,
            null,
            $filter
        );
    
        PageLayout::setTitle(_('Abschluss-Kategorien mit verwendeten Abschlüssen'));
        $this->setSidebar();
    }

    /**
     * Creates a new Abschluss-Kategorie
     */
    function kategorie_action($kategorie_id = null)
    {
        $this->abschluss_kategorie = new AbschlussKategorie($kategorie_id);
        $this->dokumente = $this->abschluss_kategorie->document_assignments;
        if ($this->abschluss_kategorie->isNew()) {
            PageLayout::setTitle(_('Neue Abschluss-Kategorie anlegen'));
            $success_message = _('Die Abschluss-Kategorie "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(sprintf(
                _('Abschluss-Kategorie: %s bearbeiten'),
                htmlReady($this->abschluss_kategorie->getDisplayName())
            ));
            $success_message = _('Die Abschluss-Kategorie "%s" wurde geändert.');
        }
        $this->sessSet(
            'dokument_target',
            [$this->abschluss_kategorie->getId(), 'AbschlussKategorie']
        );
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->abschluss_kategorie->name = Request::i18n('name')->trim();
            $this->abschluss_kategorie->name_kurz = Request::i18n('name_kurz')->trim();
            $this->abschluss_kategorie->beschreibung = Request::i18n('beschreibung')->trim();
            try {
                $stored = $this->abschluss_kategorie->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                MvvDokument::updateDocuments(
                    $this->abschluss_kategorie,
                    Request::optionArray('dokumente_items'),
                    Request::getArray('dokumente_properties')
                );
    
                PageLayout::postSuccess(sprintf(
                    $success_message, htmlReady($this->abschluss_kategorie->name)
                ));
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $this->search_dokumente = MvvDokument::getQuickSearch(
            $this->dokumente->pluck('dokument_id')
        );

        $this->cancel_url = $this->url_for('/index');

        $this->setSidebar();
        if (!$this->abschluss_kategorie->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieser Kategorie'),
                $this->url_for('shared/log_event/show/AbschlussKategorie', $this->abschluss_kategorie->getId()),
                Icon::create('log')
            )->asDialog();
        }
    }

    /**
     * Deletes a Abschluss-Kategorie
     */
    function delete_action($kategorie_id)
    {
        $abschluss_kategorie = new AbschlussKategorie($kategorie_id);
        if ($abschluss_kategorie->isNew()) {
             PageLayout::postError(_('Unbekannte Abschluss-Kategorie'));
        } else {
            if (Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();
                if (!MvvPerm::get('AbschlussKategorie')->haveFieldPerm('position')) {
                    throw new Trails_Exception(403);
                }
                if (!count($abschluss_kategorie->abschluesse)) {
                    PageLayout::postSuccess(sprintf(
                        _('Abschluss-Kategorie "%s" gelöscht!'),
                        htmlReady($abschluss_kategorie->name)
                    ));
                    $abschluss_kategorie->delete();
                } else {
                    PageLayout::postError(sprintf(
                        _('Löschen nicht möglich! Die Abschluss-Kategorie "%s" ist noch Abschlüssen zugeordnet!'),
                        htmlReady($abschluss_kategorie->name)
                    ));
                }
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * sorts abschluss kategorien
     */
    public function sort_action()
    {
        $list = Request::option('list_id');
        $orderedIds = Request::getArray('newOrder');
        if ($list === 'abschluss_kategorien') {
            if (!MvvPerm::get('AbschlussKategorie')->haveFieldPerm('position')) {
                throw new Trails_Exception(403);
            }
            $kategorien = SimpleORMapCollection::createFromArray(
                AbschlussKategorie::findBySql('1 ORDER BY position')
            );
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    $kategorie = $kategorien->find($id);
                    if ($kategorie) {
                        if ($kategorie->position != $i) {
                            $kategorie->position = $i;
                            $kategorie->store();
                        }
                        $i++;
                    }
                }
            }
        } else {
            if (!MvvPerm::get('AbschlussZuord')->haveFieldPerm('position')) {
                throw new Trails_Exception(403);
            }
            list(, $kategorie_id) = explode('_', $list);
            $abschluss_kategorie = AbschlussKategorie::find($kategorie_id);
            if ($abschluss_kategorie) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    list(, $abschluss_id) = explode('_', $id);
                    if ($abschluss_kategorie->abschluesse->find($abschluss_id)) {
                        $abschluss_zuord = new AbschlussZuord($abschluss_id);
                        if ($abschluss_zuord->position != $i) {
                            $abschluss_zuord->position = $i;
                            $abschluss_zuord->store();
                        }
                        $i++;
                    }
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    public function details_action($kategorie_id)
    {
        $this->kategorie = AbschlussKategorie::get($kategorie_id);
        $this->kategorie_id = $this->kategorie->getId();
        $this->perm_institutes = MvvPerm::getOwnInstitutes();
        if (!Request::isXhr()) {
            $this->perform_relayed('index');
            return;
        }
    }

    public function dokumente_properties_action($dokument_id)
    {
        $target = $this->sessGet('dokument_target');
        if ($target) {
            $this->redirect(
                'materialien/dokumente/ref_properties/' . $dokument_id . '/' . join('/', $target)
            );
        }
    }
    
    public function document_comments_action($dokument_id, $object_id, $object_type)
    {
        $this->redirect(
            'materialien/dokumente/ref_properties/' . join('/', [$dokument_id, $object_id, $object_type])
        );
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        if (MvvPerm::havePermCreate('AbschlussKategorie')) {
            $widget  = new ActionsWidget();
            $widget->addLink(
                _('Neue Abschluss-Kategorie anlegen'),
                $this->url_for('/kategorie'),
                Icon::create('file+add')
            );
            $sidebar->addWidget($widget);
        }
    }
    
}
