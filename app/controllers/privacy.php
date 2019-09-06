<?php
/**
 * PrivacyController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.2
 */

class PrivacyController extends AuthenticatedController
{
    /**
     * Presents the userdata of given user
     *
     * @param string $user_id
     * @param string $section
     * @throws AccessDeniedException if user has no privileges
     */
    public function index_action($user_id, $section = null)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/profile');

        $this->plugin_data = Privacy::getUserdataInformation($user_id, $section);
        $this->user_id = $user_id;
        $this->section = $section;

        $actions = Sidebar::Get()->addWidget(new ActionsWidget());
        $actions->setTitle(_('Datenschutz'));
        $actions->addLink(
            _('Anzeige Personendaten'),
            $this->url_for("privacy/landing/{$user_id}"),
            Icon::create('log', Icon::ROLE_CLICKABLE, tooltip2(_('Anzeige Personendaten')))
        )->asDialog('size=medium');
        $actions->addLink(
            _('Personendaten drucken'),
            $this->url_for('privacy/print/' . $user_id),
            Icon::create('print', Icon::ROLE_CLICKABLE, tooltip2(_('Personendaten drucken'))),
            ['class' => 'print_action', 'target' => '_blank']
        );
        $actions->addLink(
            _('Export Personendaten als CSV'),
            $this->url_for("privacy/export/{$user_id}"),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export Personendaten als CVS')))
        );
        $actions->addLink(
            _('Export persönlicher Dateien als XML'),
            $this->url_for("privacy/xml/{$user_id}"),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export Personendaten als XML')))
        );
        $actions->addLink(
            _('Export persönlicher Dateien als ZIP'),
            $this->url_for("privacy/filesexport/{$user_id}"),
            Icon::create('file-archive', Icon::ROLE_CLICKABLE, tooltip2(_('Export persönlicher Dateien als ZIP')))
        );


        $exports = Sidebar::Get()->addWidget(new ExportWidget());
        $exports->addLink(
            _('Export angezeigter Dateien als XML'),
            $this->url_for("privacy/xml/{$user_id}" . ($section ? "/{$section}" : '')),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export angezeigter Daten als XML')))
        );

        foreach ($this->plugin_data as $label => $tabledata) {
            $exports->addLink(
                htmlReady($label) . ' ' . _('CSV'),
                $this->url_for("privacy/export2csv/{$tabledata['table_name']}/{$user_id}"),
                Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(htmlReady($label) . ' CSV'))
            );
        }
    }

    /**
     * Gives access to accumulated userdata or single categories
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function landing_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/profile');

        $this->user_id  = $user_id;
        $this->sections = $this->getViewSections();

        $actions = Sidebar::Get()->addWidget(new ActionsWidget());
        $actions->setTitle(_('Datenschutz'));
        $actions->addLink(
            _('Personendaten drucken'),
            $this->url_for("privacy/print/{$user_id}"),
            Icon::create('print', Icon::ROLE_CLICKABLE, tooltip2(_('Personendaten drucken'))),
            ['class' => 'print_action', 'target' => '_blank']
        );
        $actions->addLink(
            _('Export Personendaten als CSV'),
            $this->url_for("privacy/export/{$user_id}"),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export Personendaten als CVS')))
        );
        $actions->addLink(
            _('Export persönlicher Dateien als XML'),
            $this->url_for("privacy/xml/{$user_id}"),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export Personendaten als XML')))
        );
        $actions->addLink(
            _('Export persönlicher Dateien als ZIP'),
            $this->url_for("privacy/filesexport/{$user_id}"),
            Icon::create('file-archive', Icon::ROLE_CLICKABLE, tooltip2(_('Export persönlicher Dateien als ZIP')))
        );
    }

    /**
     * Create a csv file with user data from a specific table of a plugin
     *
     * @param string $plugin_id
     * @param string $table
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function export2CSV_action($table, $user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $plugin_data = Privacy::getUserdataInformation($user_id);

        if (!empty($plugin_data)) {
            foreach($plugin_data as $table_label => $table_data) {
                if ($table_data['table_name'] !== $table) {
                    continue;
                }

                $data = $table_data['table_content'];
                $headers = array_keys($data[0]);
                $csvdata = [];
                foreach ($data as $row) {
                    $csvdata[] = array_values($row);
                }
                $this->render_csv(array_merge([$headers], $csvdata), "{$table}.csv");
                return;
            }
        }

        PageLayout::postError(_("Die Daten konnten nicht exportiert werden."));
        $this->redirect('privacy/index');
    }

    /**
     * Create a print view with user data
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function print_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        PageLayout::removeStylesheet('style.css');
        PageLayout::addStylesheet('print.css');

        $user = User::find($user_id);
        $this->plugin_data = Privacy::getUserdataInformation($user_id);
        $this->user_id = $user_id;
        $this->user_fullname = $user->getFullName();
    }

    /**
     * Create a zip file with user data
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function export_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $user = User::find($user_id);
        $plugin_data = Privacy::getUserdataInformation($user_id);
        $files = [];
        $csv = [];

        foreach ($plugin_data as $label => $table) {
            $data = $table['table_content'];
            if ($data) {
                $headers = array_keys($data[0]);
                $csvdata = [];
                foreach ($data as $row) {
                    $csvdata[] = array_values($row);
                }
                $tmpname = md5(uniqid($user_id.$table['table_name']));
                $filepath = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;
                if (array_to_csv($csvdata, $filepath, $headers)) {
                    $csv[$table['table_name']] = $filepath;
                }
            }
        }

        $tmpname = md5(uniqid('datenexport_' . $user->username));
        $zipname = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($csv as $table => $file) {
            $zip->addFile($file, $table . '.csv');
        }
        if ($zip->close()) {
            foreach ($files as $plugin => $plugin_files) {
                foreach ($plugin_files as $table => $file) {
                    unlink($file);
                }
            }
        }

        $this->render_temporary_file(
            $zipname,
            "datenexport_{$user->username}.zip",
            'application/zip'
        );
    }

    /**
     * Create a xml file with user data
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function xml_action($user_id, $section = null)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }
        $user = User::find($user_id);

        $plugin_data = Privacy::getUserdataInformation($user_id, $section);

        $xml = new SimpleXMLElement('<xml/>');
        foreach ($plugin_data as $label => $tabledata) {
            if ($tabledata['table_content']) {
                $table = $xml->addChild('table');
                $table->addChild('tablename', $tabledata['table_name']);
                foreach ($tabledata['table_content'] as $row) {
                    $tableentry = $table->addChild('tableentry');
                    foreach ($row as $key => $value){
                        $tableentry->addChild('field', $key);
                        $tableentry->addChild('value', htmlReady($value));
                    }
                }
            }
        }

        $this->set_content_type('text/xml');
        $this->response->add_header(
            'Content-disposition',
            'attachment;' . encode_header_parameter('filename', "datenexport_{$user->username}.xml")
        );
        $this->render_text($xml->asXML());

    }

    /**
     * Delivers a zip containing the files from plugins which feature the specific function
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function filesexport_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $storage = new StoredUserData($user_id);
        $user = User::find($user_id);
        $files = [];

        $tmpname = md5(uniqid('dateiexport_' . $user_id));
        $zipname = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;

        $zip = new ZipArchive();
        $zip->open($zipname, ZipArchive::CREATE);

        $avatar = Avatar::getAvatar($user_id);
        if ($avatar->is_customized()) {
            $zip->addFile($avatar->getCustomAvatarPath('normal'), $user_id . '.png');
        }

        foreach (FileRef::findByUser_id($user_id) as $fileref) {
            $storage->addFileRef($fileref);
        }

        foreach (PluginEngine::getPlugins('PrivacyPlugin') as $plugin) {
            $plugin->exportUserData($storage);
        }

         // add numbering structure to zip
        $source_files = $storage->getFileData();

        $file_names = [];
        foreach ($source_files as $k => $file_data) {
            $file_names[$file_data['name']][] = $k;
        }
        $fname_checker = [];
        do {
            $not_clear = false;
            foreach ($file_names as $fname => $dups) {
                $total_dups = count($dups);
                if ($total_dups > 1 && !in_array($fname,$fname_checker)) {
                    $name = pathinfo($fname)["filename"];
                    $ext = pathinfo($fname)["extension"];
                    for ($i = 1; $i < $total_dups; $i++) {
                        $next = $name . "[$i]." . $ext ;
                        $nodup = true;
                        do {
                            if (array_key_exists($next, $file_names)) {
                                $next_origin_new_name = pathinfo($next)["filename"] . '-origin.' . pathinfo($next)["extension"];
                                $file_names[$next_origin_new_name] = $file_names[$next];
                                unset($file_names[$next]);
                                $fname_checker[] = $next;
                            } else {
                                $nodup = false;
                            }
                        } while ($nodup);
                        $file_names[$next][] = $dups[$i];
                        $fname = $next;
                        unset($file_names[$name . '.' . $ext][$i]);
                    }
                }
            }
            foreach ($file_names as $fname => $dups) {
                $total_dups = count($dups);
                if ($total_dups > 1) {
                    $not_clear = true;
                } else {
                    $source_files[$dups[0]]['name'] = $fname;
                }
            }
        } while ($not_clear);


        foreach ($source_files as $file_data) {
            if (isset($file_data['path'])) {
                $zip->addFile($file_data['path'], $file_data['name']);
            } else {
                $zip->addFromString($file_data['name'], $file_data['contents']);
            }
        }

        $zip->close();

        if (!file_exists($zipname)) {
            PageLayout::postError(_('Keine Dateien vorhanden.'));
            $this->redirect("privacy/index/" . $user_id);
            return;
        }

        $archive_download_link = FileManager::getDownloadURLForTemporaryFile(
            $zipname,
            "dateiexport_{$user->username}.zip"
        );

        $this->redirect($archive_download_link);
    }

    /**
     * Show a message dialog to ask for user data
     *
     * @param string $user_id
     */
    public function askfor_action($user_id)
    {
        $mail_user = Config::get()->PRIVACY_CONTACT;

        $user = User::findByUsername($mail_user);
        if ($user) {
            $mail_subject = _('Auskunft nach Art 15 DSGVO');
            $mail_message = _("Sehr geehrte Damen und Herren,\n\nhiermit bitte ich Sie nach Art 15 DSGVO, mir Auskunft über die über mich gespeicherten personenbezogenen Daten zu geben.");
            $this->redirect(URLHelper::getURL('dispatch.php/messages/write', [
                'rec_uname'       => $mail_user,
                'default_subject' => $mail_subject,
                'default_body'    => $mail_message,
            ]));
        } else {
            $this->render_text(MessageBox::error(
                _('Es wurde keine Kontaktperson bestimmt.'),
                [_('Bitte wenden Sie sich an den in der Datenschutzerklärung angegebenen Ansprechpartner.'),]
            ));
        }
    }

    /**
     * Returns a list of all the sections to be displayed.
     * @return array of arrays (key => icon, title, description)
     */
    protected function getViewSections()
    {
        return [
            '' => [
                'icon'        => Icon::create('persons'),
                'title'       => _('Alle Daten'),
                'description' => _('Übersicht aller Personendaten'),
            ],
            'core' => [
                'icon'        => Icon::create('person'),
                'title'       => _('Kerndaten'),
                'description' => _('Angaben zur Person, Konfigurationen, Logs'),
            ],
            'membership' => [
                'icon'        => Icon::create('seminar'),
                'title'       => _('Veranstaltungen, Einrichtungen'),
                'description' => _('Zuordnung zu Veranstaltungen, Einrichtungen, Fächern, Studiengängen'),
            ],
            'date' => [
                'icon'        => Icon::create('date'),
                'title'       => _('Kalender/Termine'),
                'description' => _('Kalendereinträge und Termine'),
            ],
            'message' => [
                'icon'        => Icon::create('mail'),
                'title'       => _('Nachrichten'),
                'description' => _('Nachrichten, Kommentare, Blubber, News'),
            ],
            'content' => [
                'icon'        => Icon::create('forum2'),
                'title'       => _('Inhalte'),
                'description' => _('Dateien, Forum, Wiki, Literaturlisten'),
            ],
            'quest' => [
                'icon'        => Icon::create('vote'),
                'title'       => _('Fragebögen, Aufgaben'),
                'description' => _('Fragebögen, Umfragen, Aufgaben'),
            ],
            'plugins' => [
                'icon'        => Icon::create('plugin'),
                'title'       => _('Plugin-Inhalte'),
                'description' => _('Inhalte aus Plugins'),
            ],
        ];
    }


}
