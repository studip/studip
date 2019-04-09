<?php
/**
 * smileys.php - controller class for the smileys
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
require_once 'app/models/smiley.php';

class SmileysController extends AuthenticatedController
{
    const GRID_WIDTH  = 5;
    const GRID_HEIGHT = 2;

    /**
     * Common tasks for all actions.
     */
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Smiley-Übersicht'));

        $this->favorites_activated = SmileyFavorites::isEnabled()
                                     && $GLOBALS['user']->id != nobody;

        if ($this->favorites_activated) {
            $this->favorites = new SmileyFavorites($GLOBALS['user']->id);
            $this->default   = count($this->favorites->get()) > 0
                             ? 'favorites'
                             : Smiley::getFirstUsedCharacter();
        } else {
            $this->default   = Smiley::getFirstUsedCharacter();
        }
    }

    /**
     * Displays (a subset of) the smileys in the system
     *
     * @param mixed $view Subset to display, defaults to favorites if enabled
     */
    function index_action($view = null)
    {
        $this->view = $view ?: $this->default;

        $this->characters = Smiley::getUsedCharacters();
        $this->statistics = Smiley::getStatistics();

        // Redirect to index if favorites is selected but user is not logged in
        if (!$this->favorites_activated and $this->view == 'favorites') {
            $this->redirect('smileys');
        }

        $title =  _('Smiley-Übersicht') . ' - ' . sprintf(_('%s Smileys vorhanden'), $this->statistics['count_all']);
        PageLayout::setTitle($title);

        $this->smileys = $this->view == 'favorites'
                       ? Smiley::getByIds($this->favorites->get())
                       : Smiley::getGrouped($this->view);
    }

    /**
     * Toggles whether a certain smiley is favored for the current user
     *
     * @param int    $id    Id of the smiley to favor/disfavor
     * @param String $view  View to return to
     */
    function favor_action($id, $view) {
        try {
            $state = $this->favorites->toggle($id);

            $message = $state
                     ? _('Der Smiley wurde zu Ihren Favoriten hinzugefügt.')
                     : _('Der Smiley gehört nicht mehr zu Ihren Favoriten.');
            $msg_box = MessageBox::success($message);
        } catch (OutOfBoundsException $e) {
            $state = $this->favorites->contain($id);
            $message = _('Maximale Favoritenzahl erreicht. Vielleicht sollten Sie mal ausmisten? :)');
            $msg_box = MessageBox::error($message);
        }

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type', 'application/json');
            $this->render_text(json_encode([
                'state'   => $state,
                'message' => $msg_box,
            ]));
        } else {
            PageLayout::postMessage($msg_box);
            $this->redirect('smileys/index/' . $view . '#smiley' . $id);
        }
    }

    /**
     * Back end for the smiley picker javascript module.
     * Renders a list of smileys very similar to the index action but
     * unfortunately still to different to be combined.
     *
     * @param mixed $view Subset to display, defaults to favorites if enabled
     * @param int   $page Section of subset to display
     */
    function picker_action($view = null, $page = 0)
    {
        $per_page = self::GRID_WIDTH * self::GRID_HEIGHT;

        $this->view = $view ?: ($this->default === 'favorites' ? 'favorites' : 'all');
        $smileys = $this->view == 'favorites'
                 ? Smiley::getByIds($this->favorites->get())
                 : Smiley::getGrouped($this->view);

        $this->page       = $page;
        $this->pages      = floor(count($smileys) / $per_page);

        array_walk($smileys, function ($smiley) {
            $smiley->link = Smiley::getURL($smiley->name);
            $smiley->html = Smiley::img($smiley->name);
        });
        $this->smileys    = array_slice($smileys, $page * $per_page, $per_page);

        $this->characters = Smiley::getUsedCharacters();
    }
}
