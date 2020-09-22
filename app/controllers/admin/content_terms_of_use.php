<?php

/**
 * content_terms_of_use.php
 *
 * Controller for adding, editing and deleting content terms of use entries.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   data-quest Suchi & Berg GmbH
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       4.0
 */
class Admin_ContentTermsOfUseController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        if ($action === 'add') {
            $action = 'edit';
        }

        parent::before_filter($action, $args);

        // Do the permission check here since all actions are only accessible
        // for root users:
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        PageLayout::setHelpKeyword('Dateien.Nutzungsbedingungen');

        Navigation::activateItem('/admin/locations/content_terms_of_use');
    }

    /**
     * This action displays a list of all content terms of use entries.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Inhalts-Nutzungsbedingungen'));

        //build sidebar
        $actions = new ActionsWidget();
        $actions->addLink(
            _('Eintrag hinzufügen'),
            $this->url_for('admin/content_terms_of_use/add'),
            Icon::create('add')
        )->asDialog('size=auto');
        Sidebar::get()->addWidget($actions);

        //load all ContentTermsOfUse entries:
        $this->entries = ContentTermsOfUse::findBySql('1 ORDER BY position ASC, id ASC');
    }

    /**
     * This action lets a root user edit a new content terms of use entry.
     */
    public function edit_action()
    {
        $id = Request::get('entry_id') ?: null; // Convert possible empty string to null
        $this->entry = new ContentTermsOfUse($id);

        PageLayout::setTitle(
            $this->entry->isNew() ? _('Eintrag hinzufügen') : _('Eintrag bearbeiten')
        );

        if ($id !== null && $this->entry->isNew()) {
            PageLayout::postError(sprintf(
                _('Eintrag für Nutzungsbedingungen mit ID %s wurde nicht in der Datenbank gefunden!'),
                htmlReady($id)
            ));
            $this->redirect('admin/content_terms_of_use/index');
            return;
        }
    }

    public function store_action()
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $id = Request::get('id') ?: null; // Convert possible empty string to null
        $entry = new ContentTermsOfUse($id);

        $entry->id = Request::get('entry_id');
        $entry->name = Request::i18n('name');
        $entry->download_condition = Request::int('download_condition');
        $entry->icon = Request::get('icon');
        $entry->position = Request::int('position');
        $entry->description = Request::i18n('description');
        $entry->student_description = Request::i18n('student_description');
        $entry->is_default = Request::int('is_default') ?: 0;

        if (($errors = $entry->validate()) || $entry->store() === false) {
            if (count($errors) === 1) {
                PageLayout::postError(reset($errors));
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern des Eintrags für Nutzungsbedingungen'),
                    $errors
                );
            }

            $this->entry = $entry;
            $this->render_action('edit');
            return;
        }

        PageLayout::postSuccess(_('Eintrag für Nutzungsbedingungen wurde gespeichert!'));
        $this->redirect('admin/content_terms_of_use/index');
    }

    /**
     * This action lets a root user delete a new content terms of use entry.
     */
    public function delete_action()
    {
        PageLayout::setTitle(_('Eintrag löschen'));

        $this->entry_id = Request::get('entry_id');

        //load entry by looking at the entry_id:
        $entry = ContentTermsOfUse::find($this->entry_id);
        if (!$entry) {
            //entry not found: return to index page
            PageLayout::postError(sprintf(
                _('Eintrag für Nutzungsbedingungen mit ID %s wurde nicht in der Datenbank gefunden!'),
                htmlReady($this->entry_id)
            ));
            $this->redirect('admin/content_terms_of_use/index');
            return;
        }

        $this->dependent_files_count = FileRef::countBySql(
            'content_terms_of_use_id = ?',
            [$this->entry_id]
        );

        //delete was confirmed
        if (Request::isPost()) {
            if ($this->dependent_files_count > 0) {
                //files depend on the entry! We must give them
                //a new terms of use entry before we can delete
                //the entry!
                $this->other_entry_id = Request::get('other_entry_id');
                if (!$this->other_entry_id) {
                    //Files depend on the old entry, but no new entry
                    //was selected. That's an error!
                    PageLayout::postError(sprintf(
                        _('Fehler beim Löschen von Eintrag mit ID %s: Es wurde für betroffene Dateien kein neuer Nutzungsbedingungen-Eintrag ausgewählt!'),
                        htmlReady($this->entry_id)
                    ));
                    $this->redirect('admin/content_terms_of_use/index');
                    return;
                }


                //Change the content_terms_of_use_id for all file_refs
                //that have the ID of the entry which is going to be removed.
                $query = "UPDATE file_refs
                          SET content_terms_of_use_id = :new_id
                          WHERE content_terms_of_use_id = :removed_id";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':new_id', $this->other_entry_id);
                $statement->bindValue(':removed_id', $this->entry_id);
                $result = $statement->execute();

                if (!$result) {
                    PageLayout::postError(sprintf(
                        _('Fehler beim Zuordnen von Dateien zu Eintrag mit  ID %s! Eintrag mit ID %s wurde nicht gelöscht!'),
                        htmlReady($this->other_entry_id),
                        htmlReady($this->entry_id)
                    ));

                    $this->redirect('admin/content_terms_of_use/index');
                    return;
                }
            }

            //delete the entry:
            if ($entry->delete()) {
                if ($this->dependent_files_count > 0) {
                    PageLayout::postSuccess(sprintf(
                        _('Eintrag mit ID "%s" wurde gelöscht. Alle Dateien, welche diesen Eintrag verwendeten, nutzen nun den Eintrag mit ID "%s"!'),
                        htmlReady($this->entry_id),
                        htmlReady($this->other_entry_id)
                    ));
                } else {
                    PageLayout::postSuccess(sprintf(
                        _('Eintrag mit ID "%s" wurde gelöscht!'),
                        htmlReady($this->entry_id)
                    ));
                }
            } else {
                if ($this->dependent_files_count > 0) {
                    PageLayout::postError(sprintf(
                        _('Fehler beim Löschen von Eintrag mit ID "%s"! Alle Dateien, welche diesen Eintrag verwendeten, nutzen nun den Eintrag mit ID "%s"!'),
                        htmlReady($this->entry_id),
                        htmlReady($this->other_entry_id)
                    ));
                } else {
                    PageLayout::postError(sprintf(
                        _('Fehler beim Löschen von Eintrag mit ID "%s"!'),
                        htmlReady($this->entry_id)
                    ));
                }
            }

            $this->redirect('admin/content_terms_of_use/index');
            return;
        }

        // form not submitted: If files depend on it,
        // another entry must be selected for these files.
        if ($this->dependent_files_count > 0) {
            $this->other_entries = ContentTermsOfUse::findBySql(
                'id <> :entry_id ORDER BY position ASC, id ASC',
                [':entry_id' => $this->entry_id]
            );
        }
    }
}
