<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Studiengaenge_StgteilbezeichnungenController extends MVVController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem($this->me . '/studiengaenge/stgteilbezeichnungen');
        $this->action = $action;
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Alle Studiengangteil-Bezeichnungen'));
        $this->stgteilbezeichnungen = StgteilBezeichnung::getAllEnriched();
        $this->setSidebar();
    }

    /**
     * Creates a new Studiengangteil-Bezeichnung
     */
    public function stgteilbezeichnung_action($bezeichnung_id = null)
    {
        $this->stgteilbezeichnung = StgteilBezeichnung::get($bezeichnung_id);
        if ($this->stgteilbezeichnung->isNew()) {
            PageLayout::setTitle(_('Neue Studiengangteil-Bezeichnung anlegen'));
        } else {
            $this->bezeichnung_id = $this->stgteilbezeichnung->getId();
            PageLayout::setTitle(sprintf(
                _('Studiengangteil-Bezeichnung: %s bearbeiten'),
                $this->stgteilbezeichnung->name
            ));
        }

        $this->setSidebar();
        if (!$this->stgteilbezeichnung->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(
                _('Log-Einträge dieser Studiengangteil-Bezeichnung'),
                $this->url_for('shared/log_event/show/' . $this->stgteilbezeichnung->id),
                Icon::create('log'),
                ['data-dialog' => '']
            );
        }
    }

    /**
     * Store a Studiengangteil-Bezeichnung
     * @param null $bezeichnung_id
     */
    public function store_action($bezeichnung_id = null) {
        CSRFProtection::verifyUnsafeRequest();
        $stgteilbezeichnung = StgteilBezeichnung::get($bezeichnung_id);
        if ($stgteilbezeichnung->isNew()) {
            $success_message = ('Die Studiengangteil-Bezeichnung "%s" wurde angelegt.');
        } else {
            $success_message = _('Die Studiengangteil-Bezeichnung "%s" wurde geändert.');
        }
        $stored = false;
        $stgteilbezeichnung->name = Request::i18n('name')->trim();
        $stgteilbezeichnung->name_kurz = Request::i18n('name_kurz')->trim();

        $stgteilbezeichnung->verifyPermission();

        try {
            $stored = $stgteilbezeichnung->store();
        } catch (InvalidValuesException $e) {
            PageLayout::postError(htmlReady($e->getMessage()));
        }

        if ($stored !== false) {
            PageLayout::postSuccess(sprintf(
                $success_message,
                htmlReady($stgteilbezeichnung->name)
            ));
        }
        $this->relocate('studiengaenge/stgteilbezeichnungen');
    }

    /**
     * Deletes a Abschluss-Kategorie
     */
    public function delete_action($stgteilbezeichnung_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $stgteilbezeichnung = StgteilBezeichnung::get($stgteilbezeichnung_id);
        if ($stgteilbezeichnung->count_studiengaenge) {
            PageLayout::postError(sprintf(
                _('Löschen nicht möglich! Die Studiengangteil-Bezeichnung "%s" wird bereits verwendet!'),
                htmlReady($stgteilbezeichnung->name)
            ));
        } else {
            $perm = MvvPerm::get($stgteilbezeichnung);
            if (!$perm->havePerm(MvvPerm::PERM_CREATE)) {
                throw new AccessDeniedException();
            }
            PageLayout::postSuccess(sprintf(
                _('Studiengangteil-Bezeichnung "%s" gelöscht!'),
                htmlReady($stgteilbezeichnung->name)
            ));
            $stgteilbezeichnung->delete();
        }
        $this->redirect('studiengaenge/stgteilbezeichnungen');
    }

    /**
     * sorts Teilstudiengaenge
     */
    public function sort_action()
    {
        $orderedIds = Request::getArray('newOrder');
        $stgteilbezeichnungen = SimpleORMapCollection::createFromArray(
            StgteilBezeichnung::getAll()
        );

        if (is_array($orderedIds)) {
            $i = 1;
            foreach ($orderedIds as $id) {
                $stgteilbezeichnung = $stgteilbezeichnungen->find($id);
                if ($stgteilbezeichnung) {
                    if ($stgteilbezeichnung->position !== $i) {
                        $stgteilbezeichnung->position = $i;
                        $stgteilbezeichnung->store();
                    }
                    $i++;
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    /**
     * Display details
     * @param $bezeichnung_id
     * @return bool
     */
    public function details_action($bezeichnung_id)
    {
        $this->stgteilbezeichnung = StgteilBezeichnung::get($bezeichnung_id);
        $this->bezeichnung_id = $this->stgteilbezeichnung->getId();

        if (!Request::isXhr()) {
            $this->perform_relayed('stgteilbezeichnungen');
            return true;
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage(Assets::image_path('sidebar/learnmodule-sidebar.png'));

        if (MvvPerm::havePermCreate('StgteilBezeichnung')) {
            $widget  = new ActionsWidget();
            $widget->addLink(
                _('Neue Studiengangteil-Bezeichnung'),
                $this->url_for('/stgteilbezeichnung'),
                Icon::create('add')
            )->asDialog();
            $sidebar->addWidget($widget);
        }

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_("Sie können die Reihenfolge der Studiengangteil-Bezeichnungen durch Ziehen der Zeilen ändern.").'</br>'));
        $widget->addElement(new WidgetElement(_("Eine Studiengangteil-Bezeichnung kann aufgeklappt werden, um Details anzuzeigen.")));
        $helpbar->addWidget($widget);

        $this->sidebar_rendered = true;
    }
}
