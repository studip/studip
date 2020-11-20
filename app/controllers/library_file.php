<?php
/**
 * library_file.php - controller with actions for library files
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.6
 */


class LibraryFileController extends AuthenticatedController
{
    public function select_type_action($folder_id = null)
    {
        if (!Config::get()->LITERATURE_ENABLE) {
            throw new AccessDeniedException(_('Die Literaturverwaltung ist ausgeschaltet!'));
        }
        $this->folder_id = $folder_id;
        if (!$this->folder_id) {
            PageLayout::postError(_('Der Bibliothekseintrag kann nicht erstellt werden, da kein Ordner angegeben wurde!'));
            return;
        }
        $folder = Folder::find($this->folder_id);
        if (!$folder) {
            PageLayout::postError(_('Der Bibliothekseintrag kann nicht erstellt werden, da kein Ordner angegeben wurde!'));
            return;
        }
        $this->folder = $folder->getTypedFolder();
        if (!$GLOBALS['LIBRARY_VARIABLES']) {
            PageLayout::postError(_('Es sind keine Eigenschaften für Bibliothekseinträge definiert!'));
            return;
        }
        $unfiltered_document_types = $GLOBALS['LIBRARY_DOCUMENT_TYPES'];
        if (!$unfiltered_document_types) {
            PageLayout::postError(_('Es sind keine Dokumenttypen für Bibliothekseinträge definiert!'));
            return;
        }
        $this->document_types = [];
        foreach ($unfiltered_document_types as $unfiltered_type) {
            if ($unfiltered_type['properties']) {
                $this->document_types[] = $unfiltered_type;
            }
        }

        $this->user_language = getUserLanguage($GLOBALS['user']->id);
    }


    public function create_action($folder_id = null)
    {
        PageLayout::setTitle(_('Literatureintrag anlegen'));
        if (!Config::get()->LITERATURE_ENABLE) {
            throw new AccessDeniedException(_('Die Literaturverwaltung ist ausgeschaltet!'));
        }
        $this->folder_id = $folder_id;
        if (!$this->folder_id) {
            PageLayout::postError(
                _('Der Bibliothekseintrag kann nicht erstellt werden, da kein Ordner angegeben wurde!')
            );
            return;
        }
        $folder = Folder::find($this->folder_id);
        if (!$folder) {
            PageLayout::postError(
                _('Der Bibliothekseintrag kann nicht erstellt werden, da kein Ordner angegeben wurde!')
            );
            return;
        }
        $this->folder = $folder->getTypedFolder();
        CSRFProtection::verifyUnsafeRequest();
        $this->document_type_name = Request::get('document_type');
        $this->document_type = null;

        foreach ($GLOBALS['LIBRARY_DOCUMENT_TYPES'] as $defined_type) {
            if ($defined_type['name'] == $this->document_type_name) {
                $this->document_type = $defined_type;
                break;
            }
        }
        if (!$this->document_type) {
            PageLayout::postError(sprintf(
                _('Der Dokumenttyp "%s" ist nicht definiert!'),
                htmlReady($this->document_type_name))
            );
            $this->redirect('library_file/select_type');
        }

        //Get the properties:
        $this->defined_variables = $GLOBALS['LIBRARY_VARIABLES'];
        if (!$this->defined_variables) {
            PageLayout::postError(_('Es sind keine Eigenschaften für Bibliothekseinträge definiert!'));
        }

        //"enrich" the properties of the document type using the definitions:
        $this->required_properties = [];
        $this->enriched_properties = [];
        foreach ($this->defined_variables as $key => $variable) {
            if ($variable['required']) {
                $this->required_properties[] = $variable['name'];
                $key += 100;
            }
            if (in_array($variable['name'], $this->document_type['properties'])) {
                $this->enriched_properties[$key] = $variable;
                $property_types[$variable['name']] = $variable['type'];
            }
        }
        krsort($this->enriched_properties);
        $this->user_language = getUserLanguage($GLOBALS['user']->id);

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->document_properties = Request::getArray('document_properties');

            $all_empty = true;
            $empty_required = 0;
            foreach ($this->document_properties as $name => $property) {
                if (in_array($name, $this->required_properties)) {
                    if ($property_types[$name] == 'name') {
                        if (!$property[0]['family']) {
                            $empty_required++;
                        }
                    } elseif ($property_types[$name] == 'date') {
                        if (!$property['date-parts'][0][0]) {
                            $empty_required++;
                        }
                    } elseif (!trim($property)) {
                        $empty_required++;
                    }
                } else {
                    if ($property_types[$name] == 'name') {
                        if (!$property[0]['family']) {
                            unset($this->document_properties[$name]);
                        }
                    } elseif ($property_types[$name] == 'date') {
                        if (!$property['date-parts'][0][0]) {
                            unset($this->document_properties[$name]);
                        }
                    } elseif (!trim($property)) {
                        unset($this->document_properties[$name]);
                    }
                }
                if ($property) {
                    $all_empty = false;
                }
            }

            if ($all_empty) {
                PageLayout::postError(_('Es wurden keine Daten eingegeben!'));
                return;
            }
            if ($empty_required) {
                PageLayout::postError(_('Mindestens ein Pflichfeld ist leer!'));
                return;
            }

            //Filter all properties so that only those that are defined
            //for the document type are stored.
            $filtered_properties = array_intersect_key(
                $this->document_properties,
                array_fill_keys($this->document_type['properties'], true)
            );

            $document = new LibraryDocument();
            $document->type = $this->document_type['name'];
            $document->csl_data = $filtered_properties;

            $file = LibraryFile::createFromLibraryDocument(
                $document,
                $this->folder_id,
                $GLOBALS['user']->id
            );
            if ($file instanceof LibraryFile) {
                PageLayout::postSuccess(
                    sprintf(
                        _('Der Bibliothekseintrag "%s" wurde hinzugefügt!'),
                        htmlReady($file->getFilename())
                    )
                );
                //Close the dialog and reload the page:
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            }
        }
    }


    public function edit_action($file_ref_id)
    {
        PageLayout::setTitle(_('Literatureintrag bearbeiten'));
        if (!$file_ref_id) {
            PageLayout::postError(
                _('Es wurde kein Literatureintrag ausgewählt!')
            );
            return;
        }
        $this->file_ref = FileRef::find($file_ref_id);
        if (!$this->file_ref) {
            PageLayout::postError(
                _('Der gewählte Literatureintrag wurde nicht gefunden!')
            );
            return;
        }

        $this->library_file = $this->file_ref->getFileType();
        if (!($this->library_file instanceof LibraryFile)) {
            PageLayout::postError(
                _('Die gewählte Datei ist kein Bibliothekseintrag!')
            );
            return;
        }

        $this->library_document = $this->library_file->library_document;

        $this->document_type = null;
        foreach ($GLOBALS['LIBRARY_DOCUMENT_TYPES'] as $defined_type) {
            if ($defined_type['name'] == $this->library_document->type) {
                $this->document_type = $defined_type;
                break;
            }
        }
        if ($this->document_type == null) {
            PageLayout::postError(
                _('Der Bibliothekseintrag ist von einem unbekannten Dokumenttyp!')
            );
            return;
        }

        //Get the properties:
        $this->defined_variables = $GLOBALS['LIBRARY_VARIABLES'];
        if (!$this->defined_variables) {
            PageLayout::postError(_('Es sind keine Eigenschaften für Bibliothekseinträge definiert!'));
        }
        //"enrich" the properties of the document type using the definitions:
        $this->required_properties = [];
        $this->enriched_properties = [];
        foreach ($this->defined_variables as $key => $variable) {
            if ($variable['required']) {
                $this->required_properties[] = $variable['name'];
                $key += 100;
            }
            if (in_array($variable['name'], $this->document_type['properties'])) {
                $this->enriched_properties[$key] = $variable;
                $property_types[$variable['name']] = $variable['type'];
            }
        }
        krsort($this->enriched_properties);
        $this->user_language = getUserLanguage($GLOBALS['user']->id);

        $this->document_properties = $this->library_file->library_document->csl_data;

        if (Request::submitted('save')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->document_properties = Request::getArray('document_properties');

            $all_empty = true;
            $empty_required = 0;
            foreach ($this->document_properties as $name => $property) {
                if (in_array($name, $this->required_properties)) {
                    if ($property_types[$name] == 'name') {
                        if (!$property[0]['family']) {
                            $empty_required++;
                        }
                    } elseif ($property_types[$name] == 'date') {
                        if (!$property['date-parts'][0][0]) {
                            $empty_required++;
                        }
                    } elseif (!trim($property)) {
                        $empty_required++;
                    }
                } else {
                    if ($property_types[$name] == 'name') {
                        if (!$property[0]['family']) {
                            unset($this->document_properties[$name]);
                        }
                    } elseif ($property_types[$name] == 'date') {
                        if (!$property['date-parts'][0][0]) {
                            unset($this->document_properties[$name]);
                        }
                    } elseif (!trim($property)) {
                        unset($this->document_properties[$name]);
                    }
                }
                if ($property) {
                    $all_empty = false;
                }
            }
            if ($all_empty) {
                PageLayout::postError(_('Es wurden keine Daten eingegeben!'));
                return;
            }
            if ($empty_required) {
                PageLayout::postError(_('Mindestens ein Pflichfeld ist leer!'));
                return;
            }
            //Filter all properties so that only those that are defined
            //for the document type are stored.
            $filtered_properties = array_intersect_key(
                $this->document_properties,
                array_fill_keys($this->document_type['properties'], true)
            );

            $this->library_document->csl_data = $filtered_properties;
            $result = $this->library_file->updateFromLibraryDocument($this->library_document);
            if ($result) {
                PageLayout::postSuccess(
                    _('Der Bibliothekseintrag wurde aktualisiert!')
                );
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            } else {
                PageLayout::postError(
                    _('Es trat ein Fehler beim Aktualisieren des Bibliothekseintrages auf!')
                );
            }
        }
    }
}
