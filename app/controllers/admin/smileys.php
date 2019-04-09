<?php
require_once 'app/models/smiley.php';

/**
 * smileys.php - controller class for the smileys administration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author   Tobias Thelen <tthelen@uos.de>
 * @author   Jens Schmelzer <jens.schmelzer@fh-jena.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @package  smiley
 * @since    2.3
 */
class Admin_SmileysController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set navigation
        Navigation::activateItem('/admin/config/smileys');

        //pagelayout
        PageLayout::setTitle(_('Verwaltung der Smileys'));
    }

    /**
     * Administrtion view for smileys
     */
    public function index_action()
    {
        $this->view              = Request::option('view', Smiley::getFirstUsedCharacter() ?: 'a');
        $this->smileys           = Smiley::getGrouped($this->view);
        $this->favorites_enabled = SmileyFavorites::isEnabled();

        $this->setSidebar($this->view);
    }

    /**
     * Displays edit form and performs according actions upon submit
     *
     * @param int    $id   Id of the smiley to edit
     * @param String $view View to return to after editing
     */
    public function edit_action($id, $view)
    {
        PageLayout::setTitle(_('Smiley bearbeiten'));
        
        $smiley = Smiley::getById($id);

        if (Request::submitted('edit')) {
            $success = true;

            $name = Request::get('name', $smiley->name);
            if ($smiley->name != $name) { // rename smiley
                if (Smiley::getByName($name)->id) {
                    $error = sprintf(_('Es existiert bereits eine Datei mit dem Namen "%s".'), $name . '.gif');
                    PageLayout::postMessage(MessageBox::error($error));
                    $success = false;
                } elseif (!$smiley->rename($name)) {
                    $error = sprintf(_('Die Datei "%s" konnte nicht umbenannt werden.'), $smiley->name . '.gif');
                    PageLayout::postMessage(MessageBox::error($error));
                    $success = false;
                } else {
                    PageLayout::postMessage(MessageBox::success(_('Smiley erfolgreich umbenannt.')));
                }
            }

            $short = Request::get('short', $smiley->short);
            if (!$message and $smiley->short != $short) { // rename short
                if (Smiley::getByShort($short)->id) {
                    $error = sprintf(_('Es gibt bereits einen Smileys mit dem Kürzel "%s".'), $short);
                    PageLayout::postMessage(MessageBox::error($error));
                    $success = false;
                } else {
                    $smiley->short = $short;
                    $smiley->store();
                    PageLayout::postMessage(MessageBox::success(_('Kürzel erfolgreich geändert.')));
                }
            }

            if ($success) {
                $this->redirect('admin/smileys?view=' . $smiley->name{0} . '#smiley' . $smiley->id);
            } else {
                $this->redirect($this->url_for('admin/smileys/edit', $id, $view));
            }
        }

        $this->smiley = $smiley;
        $this->view   = $view;
    }

    /**
     * Deletes a smiley
     *
     * @param int    $id   Id of the smiley to delete
     * @param String $view View to return to after deletion
     * @todo needs some of confirmation
     */
    public function delete_action($id, $view)
    {
        if ($id == 'bulk') {
            $ids = Request::intArray('smiley_id');
            Smiley::remove($ids);

            $message = sprintf( _('%d Smiley(s) erfolgreich gelöscht.'), count($ids));
        } else {
            $smiley = Smiley::getById($id);
            $name = $smiley->name;
            $smiley->delete();

            $message = sprintf( _('Smiley "%s" erfolgreich gelöscht.'), $name);
        }
        PageLayout::postMessage(MessageBox::success($message));

        $this->redirect('admin/smileys?view=' . $view);
    }

    /**
     * Counts all smiley occurences systemwide and updates the smileys' counters
     *
     * @param String $view View to return to
     */
    public function count_action($view)
    {
        $updated = Smiley::updateUsage();

        $message  = sprintf(_('%d Zählerstände aktualisiert'), $updated);
        $msg = $updated > 0
            ? MessageBox::success($message)
            : MessageBox::info($message);
        PageLayout::postMessage($msg);

        $this->redirect('admin/smileys?view=' . $view);
    }

    /**
     * Refreshes the smiley table by aligning it with the file system
     *
     * @param String $view View to return to
     */
    public function refresh_action($view)
    {
        $result = Smiley::refresh();

        $message = sprintf(_('%u Operationen wurden durchgeführt.'), array_sum($result));
        $details = [
            sprintf(_('%d Smileys aktualisiert'), $result['update']),
            sprintf(_('%d Smileys eingefügt'), $result['insert']),
            sprintf(_('%d Smileys gelöscht'), $result['delete'])
        ];
        if (isset($result['favorites'])) {
            $details[] = sprintf(_('%d Favoriten geändert'), $result['favorites']);
        }
        $msg = array_sum($result) > 0
            ? MessageBox::success($message, $details, true)
            : MessageBox::info($message, $details, true);
        PageLayout::postMessage($msg);

        $this->redirect('admin/smileys?view=' . $view);
    }

    /**
     * Displays upload form and processes the upload command
     *
     * @param String $view View to return to if canceled
     */
    public function upload_action($view)
    {
        PageLayout::setTitle(_('Neues Smiley hochladen'));
        
        if (!Request::submitted('upload')) {
            $this->view = $view;
            return;
        }

        // File submitted?
        $upload = $_FILES['smiley_file'];
        if (empty($upload) or empty($upload['name'])) {
            $error = _('Sie haben keine Datei zum Hochladen ausgewählt!');
            PageLayout::postMessage(MessageBox::error($error));
            return;
        }

        // Error upon upload?
        if ($upload['error']) {
            $error = _('Es gab einen Fehler beim Upload. Bitte versuchen Sie es erneut.');
            PageLayout::postMessage(MessageBox::error($error));
            return;
        }

        // Correct mime-type?
        $no_image = !empty($upload['type']) && mb_substr($upload['type'], 0, 5) != 'image';
        if (!$no_image) {
            $image_info = getimagesize($upload['tmp_name']); // Used later on!
            $no_gif = $image_info[2] != IMAGETYPE_GIF;
        }
        if ($no_image) {
            $error = _('Die Datei ist keine Bilddatei');
            PageLayout::postMessage(MessageBox::error($error));
            return;
        }

        // Extract smiley information
        $smiley_file = $upload['name'];
        $smiley_name = mb_substr($smiley_file, 0, mb_strrpos($smiley_file, '.'));

        // Replace smiley?
        $smiley = Smiley::getByName($smiley_name);
        $replace = Request::int('replace');
        if ($smiley->id && !$replace) {
            $error = sprintf(_('Es ist bereits eine Bildatei mit dem Namen "%s" vorhanden.'), $smiley_file);
            PageLayout::postMessage(MessageBox::error($error));
            return;
        }

        // Copy file into file system
        $destination = Smiley::getFilename($smiley_file);
        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
            $error = _('Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!');
            PageLayout::postMessage(MessageBox::error($error));
            return;
        }

        // set permissions for uploaded file
        chmod($destination, 0666 & ~umask());

        // Import smiley into database
        Smiley::refresh($destination);

        // Output appropriate wurde message
        $message = $replace
                 ? sprintf(_('Die Bilddatei "%s" wurde erfolgreich ersetzt.'), $smiley_file)
                 : sprintf(_('Die Bilddatei "%s" wurde erfolgreich hochgeladen.'), $smiley_file);
        PageLayout::postMessage(MessageBox::success($message));

        // Return to index and display the view the uploaded smiley is in
        $this->redirect('admin/smileys?view=' . $smiley_file{0});
    }

    /**
     * Extends this controller with neccessary infobox
     *
     * @param String $view Currently viewed group
     */
    private function setSidebar($view)
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/smiley-sidebar.png');
        $sidebar->setTitle(PageLayout::getTitle() ?: _('Smileys'));

        // Render items
        $factory = new Flexi_TemplateFactory($this->dispatcher->trails_root . '/views/admin/smileys/');

        $actions = new ActionsWidget();
        $actions->addLink(_('Neues Smiley hochladen'), $this->url_for('admin/smileys/upload', $view), Icon::create('add', 'clickable'))->asDialog('size=auto');
        $actions->addLink(_('Smileys zählen'), $this->url_for('admin/smileys/count', $view), Icon::create('code', 'clickable'));
        $actions->addLink(_('Tabelle aktualisieren'), $this->url_for('admin/smileys/refresh', $view), Icon::create('refresh', 'clickable'));
        $actions->addLink(_('Smiley-Übersicht öffnen'), URLHelper::getLink('dispatch.php/smileys'), Icon::create('smiley', 'clickable'))->asDialog();
        $sidebar->addWidget($actions);

        $widget = new SelectWidget(_('Filter'), $this->url_for('admin/smileys/index'), 'view');
        $group = new SelectGroupElement(_('Nach Buchstaben'));
        foreach (Smiley::getUsedCharacters() as $character => $count) {
            $option = new SelectElement($character, sprintf("%s (% 2u)", mb_strtoupper($character), $count));
            $option->setActive($view == $character);
            $group->addElement($option);
        }
        $widget->addElement($group);
        
        $groups = [
            'all'   => _('Alle'),
            'top20' => _('Top 20'),
            'used'  => _('Benutzte'),
            'none'  => _('Nicht benutzte'),
            'short' => _('Nur mit Kürzel')
        ];
        $group = new SelectGroupElement(_('Gruppiert'));
        foreach ($groups as $key => $label) {
            $option = new SelectElement($key, $label);
            $option->setActive($view == $key);
            $group->addElement($option);
        }
        $widget->addElement($group);
        $sidebar->addWidget($widget);
        
        $widget = new SidebarWidget();
        $statistics = $factory->render('statistics', Smiley::getStatistics());
        $widget->setTitle(_('Statistiken'));
        $widget->addElement(new WidgetElement($statistics));
        $sidebar->addWidget($widget);
    }
}
