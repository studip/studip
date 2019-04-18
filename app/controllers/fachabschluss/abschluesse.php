<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Fachabschluss_AbschluesseController extends MVVController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/fachabschluss/abschluesse');
        $this->action = $action;
    }

    /**
     * Shows list of Abschluesse
     */
    public function index_action()
    {
        $this->initPageParams();
    
        $filter = ['mvv_fach_inst.institut_id' => MvvPerm::getOwnInstitutes()];
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        //get data
        $this->abschluesse = Abschluss::getAllEnriched(
            $this->sortby,
            $this->order,
            self::$items_per_page,
            self::$items_per_page * ($this->page - 1),
            $filter
        );
        if (count($this->abschluesse) === 0) {
            PageLayout::postInfo(_('Es wurden noch keine Abschlüsse angelegt.'));
        }
        $this->count = Abschluss::getCount($filter);
    
        PageLayout::setTitle(
            _('Abschlüsse mit verwendeten Fächern')
            . '( '
            . sprintf(ngettext('%s Abschluss', '%s Abschlüsse', $this->count), $this->count)
            . ')'
        );
        
        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie Fächer und Abschlüsse verwalten.').'</br>'));
        $widget->addElement(new WidgetElement(_('Ein Abschluss kann aufgeklappt werden, um die Fächer anzuzeigen, die diesem Abschluss bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    public function details_action($abschluss_id = null)
    {
        $this->abschluss = Abschluss::get($abschluss_id);
        $this->abschluss_id = $this->abschluss->id;
        $this->perm_institutes = MvvPerm::getOwnInstitutes();
        if (!Request::isXhr()){
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected Abschluss
     *
     * @param $abschluss_id
     */
    public function abschluss_action($abschluss_id = null)
    {
        $this->abschluss_kategorien = AbschlussKategorie::getAll();
        if (count($this->abschluss_kategorien) === 0) {
            PageLayout::postError(
                _('Es wurden noch keine Abschluss-Kategorien angelegt. Bevor Sie fortfahren, legen Sie bitte hier zunächst eine Abschluss-Kategorie an!')
            );
            $this->redirect('fachabschluss/kategorien/kategorie');
        }
        $this->abschluss = new Abschluss($abschluss_id);
        if ($this->abschluss->isNew()) {
            PageLayout::setTitle(_('Neuen Abschluss anlegen'));
            $success_message = _('Der Abschluss "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(sprintf(
                _('Abschluss: %s bearbeiten'),
                $this->abschluss->getDisplayName()
            ));
            $success_message = _('Der Abschluss "%s" wurde geändert.');
        }
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->abschluss->name = Request::i18n('name')->trim();
            $this->abschluss->name_kurz = Request::i18n('name_kurz')->trim();
            $this->abschluss->beschreibung = Request::i18n('beschreibung')->trim();
            $this->abschluss->assignKategorie(Request::option('kategorie_id'));
            try {
                $stored = $this->abschluss->store(true);
            } catch (InvalidValuesException $e) {
                 PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                if ($stored) {
                    PageLayout::postSuccess(sprintf(
                        $success_message,
                        htmlReady($this->abschluss->name)
                    ));
                } else {
                    PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $this->setSidebar();
        if (!$this->abschluss->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieses Abschlusses'),
                $this->url_for('shared/log_event/show/Abschluss/' . $this->abschluss->getId()),
                Icon::create('log')
            )->asDialog();
        }
    }

    /**
     * Deletes the Abschluss
     */
    public function delete_action($abschluss_id)
    {
        $abschluss = Abschluss::get($abschluss_id);
        if (Request::submitted('delete')) {
            if ($abschluss->isNew()) {
                PageLayout::postError(_('Der Abschluss kann nicht gelöscht werden (unbekannter Abschluss).'));
            } else {
                CSRFProtection::verifyUnsafeRequest();
                $name = $abschluss->name;
                $abschluss->delete();
                PageLayout::postSuccess(sprintf(
                    _('Der Abschluss "%s" wurde gelöscht.'),
                    htmlReady($name)
                ));
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    public function fach_action($fach_id = null)
    {
        $response = $this->relay('fachabschluss/faecher/fach/' . $fach_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            if ($response->headers['Location']) {
                $this->redirect($response->headers['Location']);
            } else {
              $this->relocate('fachabschluss/faecher/fach/' . $fach_id);
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

        if (MvvPerm::havePermCreate('Abschluss')) {
            $widget  = new ActionsWidget();
            $widget->addLink(
                _('Neuen Abschluss anlegen'),
                $this->url_for('/abschluss'),
                    Icon::create('file')
            );
            $sidebar->addWidget($widget);
        }
        $this->sidebar_rendered = true;
    }
}
